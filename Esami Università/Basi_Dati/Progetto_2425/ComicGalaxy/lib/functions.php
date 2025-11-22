<?php


function login($user, $psw) {
    $db = open_pg_connection();
    
    $sql = "SELECT u.mail,
                CASE 
                    WHEN c.mail IS NOT NULL THEN 'cliente'
                   WHEN m.mail IS NOT NULL THEN 'manager'
                   ELSE 'sconosciuto'
                END AS ruolo,
            COALESCE(c.nome, m.nome) AS nome,
            COALESCE(c.cognome, m.cognome) AS cognome,
            u.telefono
            FROM comicgalaxy.utente u
            LEFT JOIN comicgalaxy.cliente c ON u.mail = c.mail
            LEFT JOIN comicgalaxy.manager m ON u.mail = m.mail
            WHERE u.mail = $1 AND u.password = $2";
    
    $params = array($user, $psw);
    $result = pg_prepare($db, "check_user", $sql);
    $result = pg_execute($db, "check_user", $params);

    $logged = null;
    if ($row = pg_fetch_assoc($result)) {
        $logged = array(
            'mail' => $row['mail'],
            'ruolo' => $row['ruolo'],
            'nome' => $row['nome'],
            'cognome' => $row['cognome'],
            'telefono' => $row['telefono']
        );
    }

    close_pg_connection($db);
    return $logged;
}

function getNegozio($mail){
    include_once('lib/functions.ini.php');

    $db = open_pg_connection();

    $sql = "SELECT id, nome
            FROM comicgalaxy.negozio
            WHERE manager = $1";

    $param = array($mail);
    $result = pg_prepare($db, "get_negozio", $sql);
    $result = pg_execute($db, "get_negozio", $param);

    $negozio = null;
    if ($row = pg_fetch_assoc($result)) {
        $negozio = array(
            'id' => $row['id'],
            'nome' => $row['nome']
        );
    }

    close_pg_connection($db);
    return $negozio;
}

function getProdotti($id_negozio){
    include_once('lib/functions.ini.php');

    $db = open_pg_connection();

    $sql = "SELECT p.id, p.nome, prezzo, quantita
            FROM comicgalaxy.prodotto as p join comicgalaxy.fornitura_negozio as f on p.id = f.id_prodotto
            WHERE f.id_negozio = $1
            ORDER BY p.nome";

    $param = array($id_negozio);
    $result = pg_prepare($db, "get_prodotti", $sql);
    $result = pg_execute($db, "get_prodotti", $param);

    $prodotti = array();
    while ($row = pg_fetch_assoc($result)) {
        $prodotti[] = array(
            'id_prodotto' => $row['id'],
            'nome' => $row['nome'],
            'prezzo' => $row['prezzo'],
            'quantita' => $row['quantita']
        );
    }

    close_pg_connection($db);
    return $prodotti;
}

function getOrarioNegozio($id_negozio){
    include_once('lib/functions.ini.php');

    $db = open_pg_connection();

    $sql = "SELECT giorno, ora_apertura, ora_chiusura
            FROM comicgalaxy.orario
            WHERE id_negozio = $1
            order by giorno";

    $param = array($id_negozio); 
    $result = pg_prepare($db, "get_orario", $sql);
    $result = pg_execute($db, "get_orario", $param);

    $orari = array();
    while ($row = pg_fetch_assoc($result)) {
        $orari[] = array(
            'giorno' => $row['giorno'],
            'ora_apertura' => $row['ora_apertura'],
            'ora_chiusura' => $row['ora_chiusura']
        );
    }
    close_pg_connection($db);
    return $orari;
}

function aggiornaOrario($id_negozio, $giorno, $apertura, $chiusura,$chiuso=false){
    include_once('lib/functions.ini.php');

    $db = open_pg_connection();

    if ($chiuso) {
        $apertura = null;
        $chiusura = null;
    }

    $sql = "UPDATE comicgalaxy.orario
            SET ora_apertura = $1, ora_chiusura = $2
            WHERE id_negozio = $3 AND giorno = $4";

    $params = array($apertura, $chiusura, $id_negozio, $giorno);
    $result = pg_prepare($db, "update_orario", $sql);
    $result = pg_execute($db, "update_orario", $params);

    close_pg_connection($db);

    return $result;
}

function aggiornaPrezzoProdotto($id_negozio, $id_prodotto, $prezzo){
    include_once('lib/functions.ini.php');

    $db = open_pg_connection();

    $sql = "UPDATE comicgalaxy.fornitura_negozio
            SET prezzo = $1
            WHERE id_negozio = $2 AND id_prodotto = $3";

    $params = array($prezzo, $id_negozio, $id_prodotto);
    $result = pg_prepare($db, "update_prezzo", $sql);
    $result = pg_execute($db, "update_prezzo", $params);

    close_pg_connection($db);
    var_dump($id_prodotto);
    return $result;
}

function eliminaFornituraProdotto($id_negozio, $id_prodotto){
    include_once('lib/functions.ini.php');

    $db = open_pg_connection();

    $sql = "DELETE FROM comicgalaxy.fornitura_negozio
            WHERE id_negozio = $1 AND id_prodotto = $2";

    $params = array($id_negozio, $id_prodotto);
    $result = pg_prepare($db, "delete_fornitura", $sql);
    $result = pg_execute($db, "delete_fornitura", $params);

    close_pg_connection($db);
    return $result;
}
function getOrdiniNegozio($id_negozio){
    date_default_timezone_set('Europe/Rome');
    include_once('lib/functions.ini.php');

    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.v_storico_ordini_fornitori
            WHERE id_negozio = $1
            ORDER BY ritirato ";

    $param = array($id_negozio);
    pg_prepare($db, "get_ordini", $sql);
    $result = pg_execute($db, "get_ordini", $param);

    $ordini = array();

    while ($row = pg_fetch_assoc($result)) {

        $oggi = date('Y-m-d');
        $dataConsegna = $row['data_consegna'];
        $ritirato = $row['ritirato'];
        if ($ritirato === 't' || $ritirato === true) {
            $stato = "Ritirato";
        } 
        else if ($dataConsegna === $oggi) {
            $stato = "Da ritirare";
        } 
        else {
            $stato = "In arrivo";
        }

        $ordini[] = array(
            'id' => $row['id_ordine'],
            'data_ordine' => $row['data_ordine'],
            'data_consegna' => $row['data_consegna'],
            'totale' => $row['totale'],
            'stato' => $stato
        );
    }

    close_pg_connection($db);
    return $ordini;
}

function ritiraOrdine($id_ordine){
    include_once('lib/functions.ini.php');

    $db = open_pg_connection();

    $sql = "UPDATE comicgalaxy.ordine
            SET ritirato = TRUE
            WHERE id = $1";

    $params = array($id_ordine);
    $result = pg_prepare($db, "ritira_ordine", $sql);
    $result = pg_execute($db, "ritira_ordine", $params);

    close_pg_connection($db);
    return $result;
}

function getProdottiOrdinabili(){
    include_once('lib/functions.ini.php');

    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.v_prodotti_ordinabili
            ORDER BY nome_prodotto";

    $result = pg_prepare($db, "get_prodotti_ordinabili", $sql);
    $result = pg_execute($db, "get_prodotti_ordinabili", array());

    $prodotti = array();
    while ($row = pg_fetch_assoc($result)) {
        $prodotti[] = array(
            'id_prodotto' => $row['id'],
            'nome_prodotto' => $row['nome_prodotto'],
            'prezzo' => $row['prezzo'],
            'quantita' => $row['quantita'],
            'nome_fornitore' => $row['nome_fornitore']
        );
    }
    close_pg_connection($db);
    return $prodotti;
}

function creaOrdine($id_negozio, $id_prodotti, $quantita) {
    include_once('lib/functions.ini.php'); // Per open_pg_connection

    $db = open_pg_connection();

    // Converte gli array PHP in array PostgreSQL
    $prodotti_pg = '{' . implode(',', $id_prodotti) . '}';
    $quantita_pg = '{' . implode(',', $quantita) . '}';

    // Prepara ed esegue la funzione PL/pgSQL
    $sql = "SELECT comicgalaxy.ordina_prodotti($1, $2::int[], $3::int[])";
    $params = array($id_negozio, $prodotti_pg, $quantita_pg);
    
    $result = pg_prepare($db, "ordina_prodotti", $sql);
    $result = pg_execute($db, "ordina_prodotti", $params);

    close_pg_connection($db);

    return $result !== false; // ritorna true se eseguito correttamente
}


function getProdottoById($id_prodotto){
    include_once('lib/functions.ini.php');

    $db = open_pg_connection();

    $sql = "SELECT nome
            FROM comicgalaxy.prodotto
            WHERE id = $1";

    $param = array($id_prodotto);
    $result = pg_prepare($db, "get_prodotto_by_id", $sql);
    $result = pg_execute($db, "get_prodotto_by_id", $param);

    $prodotto = null;
    if ($row = pg_fetch_assoc($result)) {
        $prodotto = array(
            'nome' => $row['nome']
        );
    }

    close_pg_connection($db);
    return $prodotto;
}

function getSubtotaleProdotto($id_prodotto, $quantita){
    include_once('lib/functions.ini.php');

    $db = open_pg_connection();

    $sql = "SELECT comicgalaxy.riepilogo_prodotto($1,$2)";

    $param = array($id_prodotto, $quantita);
    $result = pg_prepare($db, "get_prezzo_prodotto", $sql);
    $result = pg_execute($db, "get_prezzo_prodotto", $param);

    $prezzo = 0;
    if ($row = pg_fetch_assoc($result)) {
        $prezzo = $row['riepilogo_prodotto'];
    }

    close_pg_connection($db);
    return $prezzo;
}

