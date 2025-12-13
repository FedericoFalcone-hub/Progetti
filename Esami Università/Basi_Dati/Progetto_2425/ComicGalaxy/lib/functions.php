<?php

require_once __DIR__ . '/functions.ini.php';
function login($user, $psw)
{
    $db = open_pg_connection();

    // Recupera l'hash della password e i dati dell'utente
    $sql = "SELECT u.mail,
                   u.password,
                   CASE 
                       WHEN c.mail IS NOT NULL THEN 'cliente'
                       WHEN m.mail IS NOT NULL THEN 'manager'
                       ELSE 'sconosciuto'
                   END AS ruolo,
                   COALESCE(c.nome, m.nome) AS nome,
                   COALESCE(c.cognome, m.cognome) AS cognome,
                   u.telefono,
                   u.sospeso,
                   c.cf
            FROM comicgalaxy.utente u
            LEFT JOIN comicgalaxy.cliente c ON u.mail = c.mail
            LEFT JOIN comicgalaxy.manager m ON u.mail = m.mail
            WHERE u.mail = $1";

    $params = array($user);
    $result = pg_prepare($db, "check_user", $sql);
    $result = pg_execute($db, "check_user", $params);

    $logged = null;
    if ($row = pg_fetch_assoc($result)) {
        // Verifica la password hashata
        if (password_verify($psw, $row['password'])) {
            $logged = array(
                'mail' => $row['mail'],
                'ruolo' => $row['ruolo'],
                'nome' => $row['nome'],
                'cognome' => $row['cognome'],
                'telefono' => $row['telefono'],
                'sospeso' => $row['sospeso'],
                'cf' => $row['cf']
            );
        }
    }

    close_pg_connection($db);
    return $logged;
}


function getNegozio($mail)
{

    $db = open_pg_connection();

    $sql = "SELECT n.id, nome, data_chiusura, citta, via, civico
            FROM comicgalaxy.negozio n INNER JOIN comicgalaxy.indirizzo i ON n.id_indirizzo = i.id
            WHERE manager = $1";

    $param = array($mail);
    $result = pg_prepare($db, "get_negozio", $sql);
    $result = pg_execute($db, "get_negozio", $param);

    $negozio = null;
    if ($row = pg_fetch_assoc($result)) {
        $negozio = array(
            'id' => $row['id'],
            'data_chiusura' => $row['data_chiusura'],
            'nome' => $row['nome'],
            'citta' => $row['citta'],
            'via' => $row['via'],
            'civico' => $row['civico']
        );
    }

    close_pg_connection($db);
    return $negozio;
}

function getProdotti($id_negozio)
{


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

function getOrarioNegozio($id_negozio)
{


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

function aggiornaOrario($id_negozio, $giorno, $apertura, $chiusura, $chiuso = false)
{


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

function aggiornaPrezzoProdotto($id_negozio, $id_prodotto, $prezzo)
{


    $db = open_pg_connection();

    $sql = "UPDATE comicgalaxy.fornitura_negozio
            SET prezzo = $1
            WHERE id_negozio = $2 AND id_prodotto = $3";

    $params = array($prezzo, $id_negozio, $id_prodotto);
    $result = pg_prepare($db, "update_prezzo", $sql);
    $result = pg_execute($db, "update_prezzo", $params);

    close_pg_connection($db);
    return $result;
}

function eliminaFornituraProdotto($id_negozio, $id_prodotto)
{

    $db = open_pg_connection();

    $sql = "DELETE FROM comicgalaxy.fornitura_negozio
            WHERE id_negozio = $1 AND id_prodotto = $2";

    $params = array($id_negozio, $id_prodotto);
    $result = pg_prepare($db, "delete_fornitura", $sql);
    $result = pg_execute($db, "delete_fornitura", $params);

    close_pg_connection($db);
    return $result;
}
function getOrdiniNegozio($id_negozio)
{
    date_default_timezone_set('Europe/Rome');


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
        } else if ($dataConsegna <= $oggi) {
            $stato = "Da ritirare";
        } else {
            $stato = "In arrivo";
        }

        $ordini[] = array(
            'id' => $row['id_ordine'],
            'nome_fornitore' => $row['nome'],
            'data_ordine' => $row['data_ordine'],
            'data_consegna' => $row['data_consegna'],
            'totale' => $row['totale'],
            'stato' => $stato
        );
    }

    close_pg_connection($db);
    return $ordini;
}

function ritiraOrdine($id_ordine)
{


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

function getProdottiOrdinabili()
{


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
            'prezzo' => $row['prezzo_medio'],
            'quantita' => $row['quantita_totale']
        );
    }
    close_pg_connection($db);
    return $prodotti;
}

function creaOrdine($id_negozio, $id_prodotti, $quantita)
{

    $db = open_pg_connection();

    // Converte gli array PHP in array PostgreSQL
    $prodotti_pg = '{' . implode(',', $id_prodotti) . '}';
    $quantita_pg = '{' . implode(',', $quantita) . '}';

    // Prepara ed esegue la funzione PL/pgSQL
    $sql = "SELECT comicgalaxy.ordina_prodotti($1, $2::int[], $3::int[])";
    $params = array($id_negozio, $prodotti_pg, $quantita_pg);

    $result = pg_prepare($db, "ordina_prodotti", $sql);
    $result = pg_execute($db, "ordina_prodotti", $params);

    if (!$result) {
        $error = pg_last_error($db); // cattura l'errore dal database
        close_pg_connection($db);
        return $error;  // restituisci l'errore
    }

    close_pg_connection($db);
    return true;
}

function getProdottoById($id_prodotto)
{

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

function getSubtotaleProdotto($id_prodotto, $quantita)
{


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

function getDettaglioOrdine($id_ordine)
{

    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.v_storico_ordini_fornitori
            WHERE id_ordine = $1";

    $param = array($id_ordine);
    $result = pg_prepare($db, "get_dettaglio_ordine", $sql);
    $result = pg_execute($db, "get_dettaglio_ordine", $param);

    while ($row = pg_fetch_assoc($result)) {

        $oggi = date('Y-m-d');
        $dataConsegna = $row['data_consegna'];
        $ritirato = $row['ritirato'];
        if ($ritirato === 't' || $ritirato === true) {
            $stato = "Ritirato";
        } else if ($dataConsegna <= $oggi) {
            $stato = "Da ritirare";
        } else {
            $stato = "In arrivo";
        }

        $ordine = array(
            'data_ordine' => $row['data_ordine'],
            'nome_fornitore' => $row['nome'],
            'data_consegna' => $row['data_consegna'],
            'prezzo' => $row['prezzo'],
            'totale' => $row['totale'],
            'stato' => $stato
        );
    }

    close_pg_connection($db);
    return $ordine;
}
function getProdottiOrdine($id_ordine)
{

    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.v_storico_prodotti_ordine
            WHERE id= $1";

    $param = array($id_ordine);
    $result = pg_prepare($db, "get_prodotti_ordine", $sql);
    $result = pg_execute($db, "get_prodotti_ordine", $param);

    $prodotti = array();
    while ($row = pg_fetch_assoc($result)) {
        $prodotti[] = array(
            'cf_cliente' => $row['cf_cliente'],
            'nome_prodotto' => $row['nome_prodotto'],
            'nome_fornitore' => $row['nome_fornitore'],
            'prezzo' => $row['prezzo'],
            'quantita' => $row['quantita'],
            'totale' => $row['totale']
        );
    }

    close_pg_connection($db);
    return $prodotti;
}

function getClientiNegozio($id_negozio)
{

    $db = open_pg_connection();

    $sql = "SELECT  *
            FROM comicgalaxy.v_clienti  
            WHERE id_negozio = $1 and sospeso=false
            ORDER BY cognome, nome";

    $param = array($id_negozio);
    $result = pg_prepare($db, "get_clienti_negozio", $sql);
    $result = pg_execute($db, "get_clienti_negozio", $param);

    $clienti = array();
    while ($row = pg_fetch_assoc($result)) {
        if ($row['data_scadenza'] === null) {
            $data_scadenza = "N/A";
        } else {
            $data_scadenza = $row['data_scadenza'];
        }

        if ($row['saldo'] === null) {
            $saldo = 0;
        } else {
            $saldo = $row['saldo'];
        }

        if ($row['data_emissione'] === null) {
            $data_emissione = "N/A";
        } else {
            $data_emissione = $row['data_emissione'];
        }

        $clienti[] = array(
            'cf_cliente' => $row['cf_cliente'],
            'mail' => $row['mail'],
            'nome' => $row['nome'],
            'cognome' => $row['cognome'],
            'data_emissione' => $data_emissione,
            'data_scadenza' => $data_scadenza,
            'saldo' => $saldo,
            'telefono' => $row['telefono'],

        );
    }

    close_pg_connection($db);
    return $clienti;
}

function aggiornaUtente($old_mail, $mail, $nome, $cognome, $telefono)
{
    $db = open_pg_connection();

    $sql = "SELECT comicgalaxy.aggiorna_utente($1, $2, $3, $4, $5)";
    $params = array($old_mail, $mail, $nome, $cognome, $telefono);
    $result = pg_prepare($db, "update_utente", $sql);
    $result = pg_execute($db, "update_utente", $params);



    if (!$result) {
        $result = pg_last_error($db);
    }
    close_pg_connection($db);
    return $result;
}

function chiudiNegozioDefinitivamente($id_negozio)
{
    $db = open_pg_connection();

    $sql = "UPDATE comicgalaxy.negozio
            SET data_chiusura = CURRENT_DATE
            WHERE id = $1";

    $params = array($id_negozio);

    $result = pg_query_params($db, $sql, $params);

    if (!$result) {
        echo "PG ERROR: " . pg_last_error($db);
    }


    close_pg_connection($db);
    return $result;
}

function getUtenti()
{
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
            ORDER BY ruolo, cognome, nome";

    $result = pg_prepare($db, "get_utenti", $sql);
    $result = pg_execute($db, "get_utenti", array());
    $utenti = array();
    while ($row = pg_fetch_assoc($result)) {
        $utenti[] = array(
            'mail' => $row['mail'],
            'nome' => $row['nome'],
            'cognome' => $row['cognome'],
            'ruolo' => $row['ruolo'],
            'telefono' => $row['telefono']
        );
    }

    close_pg_connection($db);
    return $utenti;
}

function getUtente($mail)
{
    $db = open_pg_connection();

    $sql = "SELECT u.mail,
                CASE 
                    WHEN c.mail IS NOT NULL THEN 'cliente'
                   WHEN m.mail IS NOT NULL THEN 'manager'
                   ELSE 'sconosciuto'
                END AS ruolo,
            COALESCE(c.nome, m.nome) AS nome,
            COALESCE(c.cognome, m.cognome) AS cognome,
            u.telefono,
            u.sospeso
            FROM comicgalaxy.utente u
            LEFT JOIN comicgalaxy.cliente c ON u.mail = c.mail
            LEFT JOIN comicgalaxy.manager m ON u.mail = m.mail
            WHERE u.mail = $1";

    $param = array($mail);
    $result = pg_prepare($db, "get_utente", $sql);
    $result = pg_execute($db, "get_utente", $param);

    $utente = null;
    if ($row = pg_fetch_assoc($result)) {
        $utente = array(
            'mail' => $row['mail'],
            'nome' => $row['nome'],
            'cognome' => $row['cognome'],
            'ruolo' => $row['ruolo'],
            'telefono' => $row['telefono'],
            'sospeso' => $row['sospeso']
        );
    }

    close_pg_connection($db);
    return $utente;
}

function getClienti()
{
    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.cliente c INNER JOIN comicgalaxy.utente u ON c.mail = u.mail
            ORDER BY c.nome, c.cognome";

    $result = pg_prepare($db, "get_utenti", $sql);
    $result = pg_execute($db, "get_utenti", array());
    $utenti = array();

    while ($row = pg_fetch_assoc($result)) {
        $utenti[] = array(
            'mail' => $row['mail'],
            'nome' => $row['nome'],
            'cognome' => $row['cognome'],
            'telefono' => $row['telefono'],
            'sospeso' => $row['sospeso']
        );
    }

    close_pg_connection($db);
    return $utenti;
}

function crea_cliente($mail, $nome, $cognome, $telefono, $password, $cf)
{
    $db = open_pg_connection();
    $psw_hash = password_hash($password, PASSWORD_BCRYPT);
    $sql = "SELECT comicgalaxy.crea_cliente($1, $2, $3, $4, $5, $6)";
    $params = array($mail, $nome, $cognome, $telefono, $psw_hash, $cf);

    try {
        $prep = pg_prepare($db, "create_cliente", $sql);
        if (!$prep) {
            $err = pg_last_error($db);
            close_pg_connection($db);
            return $err;
        }

        $res = pg_execute($db, "create_cliente", $params);
        if (!$res) {
            $err = pg_last_error($db);
            close_pg_connection($db);
            return $err;
        }

        close_pg_connection($db);
        return true;
    } catch (Exception $e) {
        close_pg_connection($db);
        return $e->getMessage();
    }
}


function sospendi_utente($mail)
{
    $db = open_pg_connection();

    $sql = "select comicgalaxy.sospendi_utente($1)";

    $params = array($mail);
    try {
        $prep = pg_prepare($db, "sospendi_utente", $sql);
        if (!$prep) {
            $err = pg_last_error($db);
            close_pg_connection($db);
            return $err;
        }

        $res = pg_execute($db, "sospendi_utente", $params);
        if (!$res) {
            $err = pg_last_error($db);
            close_pg_connection($db);
            return $err;
        }

        close_pg_connection($db);
        return true;
    } catch (Exception $e) {
        close_pg_connection($db);
        return $e->getMessage();
    }
}


function riattiva_utente($mail)
{
    $db = open_pg_connection();

    $sql = "UPDATE comicgalaxy.utente
            SET sospeso = FALSE
            WHERE mail = $1";

    $params = array($mail);
    $result = pg_prepare($db, "riattiva_utente", $sql);
    $result = pg_execute($db, "riattiva_utente", $params);

    close_pg_connection($db);
    return $result;
}

function getManager()
{
    $db = open_pg_connection();

    $sql = "SELECT m.mail,
                   m.nome,
                   m.cognome,
                   u.telefono,
                   n.nome AS negozio,
                   u.sospeso
            FROM comicgalaxy.manager m INNER JOIN comicgalaxy.utente u ON m.mail = u.mail LEFT JOIN comicgalaxy.negozio n ON n.manager = m.mail
            ORDER BY m. nome, m.cognome";

    $result = pg_prepare($db, "get_manager", $sql);
    $result = pg_execute($db, "get_manager", array());
    $managers = array();

    while ($row = pg_fetch_assoc($result)) {
        $managers[] = array(
            'mail' => $row['mail'],
            'nome' => $row['nome'],
            'cognome' => $row['cognome'],
            'telefono' => $row['telefono'],
            'negozio' => $row['negozio'],
            'sospeso' => $row['sospeso']
        );
    }

    close_pg_connection($db);
    return $managers;
}

function crea_manager($mail, $nome, $cognome, $telefono, $password)
{
    $db = open_pg_connection();
    $psw_hash = password_hash($password, PASSWORD_BCRYPT);
    $sql = "SELECT comicgalaxy.crea_manager($1, $2, $3, $4, $5)";
    $params = array($mail, $nome, $cognome, $telefono, $psw_hash);

    try {
        $prep = pg_prepare($db, "create_manager", $sql);
        if (!$prep) {
            $err = pg_last_error($db);
            close_pg_connection($db);
            return $err;
        }

        $res = pg_execute($db, "create_manager", $params);
        if (!$res) {
            $err = pg_last_error($db);
            close_pg_connection($db);
            return $err;
        }

        close_pg_connection($db);
        return true;
    } catch (Exception $e) {
        close_pg_connection($db);
        return $e->getMessage();
    }
}

function getNegozi()
{
    $db = open_pg_connection();

    $sql = "SELECT n.id, n.nome, n.data_chiusura, i.citta, i.via, i.civico, m.nome AS nome_manager, m.cognome AS cognome_manager
            FROM comicgalaxy.negozio n
            JOIN comicgalaxy.indirizzo i ON n.id_indirizzo = i.id
            LEFT JOIN comicgalaxy.manager m ON n.manager = m.mail
            ORDER BY n.nome";

    $result = pg_prepare($db, "get_negozi", $sql);
    $result = pg_execute($db, "get_negozi", array());
    $negozi = array();

    while ($row = pg_fetch_assoc($result)) {
        $negozi[] = array(
            'id' => $row['id'],
            'nome' => $row['nome'],
            'data_chiusura' => $row['data_chiusura'],
            'indirizzo' => $row['citta'] . ', ' . $row['via'] . ', ' . $row['civico'],
            'manager' => $row['nome_manager'] . ' ' . $row['cognome_manager']
        );
    }


    close_pg_connection($db);
    return $negozi;
}

function getManagerLiberi()
{
    $db = open_pg_connection();

    $sql = "SELECT m.mail, m.nome, m.cognome
            FROM comicgalaxy.manager m INNER JOIN comicgalaxy.utente u ON m.mail = u.mail
            LEFT JOIN comicgalaxy.negozio n ON m.mail = n.manager
            WHERE n.manager IS NULL and u.sospeso = FALSE
            ORDER BY m.nome, m.cognome";

    $result = pg_prepare($db, "get_manager_liberi", $sql);
    $result = pg_execute($db, "get_manager_liberi", array());
    $managers = array();

    while ($row = pg_fetch_assoc($result)) {
        $managers[] = array(
            'mail' => $row['mail'],
            'nome' => $row['nome'],
            'cognome' => $row['cognome']
        );
    }

    close_pg_connection($db);
    return $managers;
}

function crea_negozio($nome, $manager, $citta, $via, $civico, $telefono)
{
    $db = open_pg_connection();

    $sql = "SELECT comicgalaxy.crea_negozio($1, $2, $3, $4, $5, $6)";
    $params = array($nome, $manager, $citta, $via, $civico, $telefono);

    // prepara la query
    $prep = pg_prepare($db, "create_negozio", $sql);
    if (!$prep) {
        $err = pg_last_error($db);
        close_pg_connection($db);
        return $err;  // ritorna il messaggio d'errore
    }

    // esegui la query
    $res = pg_execute($db, "create_negozio", $params);
    if (!$res) {
        $err = pg_last_error($db);
        close_pg_connection($db);
        return $err;  // ritorna il messaggio d'errore
    }

    close_pg_connection($db);
    return true;  // tutto ok
}

function aggiorna_manager($id_negozio, $manager)
{
    $db = open_pg_connection();

    $sql = "UPDATE comicgalaxy.negozio
            SET manager = $1
            WHERE id = $2";

    $params = array($manager, $id_negozio);
    $result = pg_prepare($db, "update_manager", $sql);
    $result = pg_execute($db, "update_manager", $params);

    close_pg_connection($db);
    return $result;
}

function getFornitori()
{
    $db = open_pg_connection();

    $sql = "SELECT p_iva, nome, telefono, mail, via, civico, citta, sospeso
            FROM comicgalaxy.fornitore INNER JOIN comicgalaxy.indirizzo ON fornitore.indirizzo = indirizzo.id
            ORDER BY nome";

    $result = pg_prepare($db, "get_fornitori", $sql);
    $result = pg_execute($db, "get_fornitori", array());
    $fornitori = array();

    while ($row = pg_fetch_assoc($result)) {
        $fornitori[] = array(
            'p_iva' => $row['p_iva'],
            'nome' => $row['nome'],
            'telefono' => $row['telefono'],
            'mail' => $row['mail'],
            'indirizzo' => $row['via'] . ' ' . $row['civico'] . ', ' . $row['citta'],
            'sospeso' => $row['sospeso']
        );
    }

    close_pg_connection($db);
    return $fornitori;
}

function getProdottiFornitore($p_iva)
{
    $db = open_pg_connection();

    $sql = "SELECT p.id, p.nome, f.prezzo, f.quantita
            FROM comicgalaxy.fornitura_fornitore f inner join comicgalaxy.prodotto p on f.id_prodotto = p.id
            WHERE p_iva_fornitore = $1
            ORDER BY nome";

    $param = array($p_iva);
    $result = pg_prepare($db, "get_prodotti_fornitore", $sql);
    $result = pg_execute($db, "get_prodotti_fornitore", $param);

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

function getFornitoreByPIVA($p_iva)
{
    $db = open_pg_connection();

    $sql = "SELECT nome, telefono, mail, via, civico, citta
            FROM comicgalaxy.fornitore INNER JOIN comicgalaxy.indirizzo ON fornitore.indirizzo = indirizzo.id
            WHERE p_iva = $1";

    $param = array($p_iva);
    $result = pg_prepare($db, "get_fornitore_by_piva", $sql);
    $result = pg_execute($db, "get_fornitore_by_piva", $param);

    $fornitore = null;
    if ($row = pg_fetch_assoc($result)) {
        $fornitore = $row;
    }

    close_pg_connection($db);
    return $fornitore;
}

function sospendi_fornitore($p_iva)
{
    $db = open_pg_connection();

    $sql = "UPDATE comicgalaxy.fornitore
            SET sospeso = TRUE
            WHERE p_iva = $1";

    $params = array($p_iva);
    $result = pg_prepare($db, "sospendi_fornitore", $sql);
    $result = pg_execute($db, "sospendi_fornitore", $params);
    close_pg_connection($db);
    return $result;
}


function riattiva_fornitore($p_iva)
{
    $db = open_pg_connection();

    $sql = "UPDATE comicgalaxy.fornitore
            SET sospeso = FALSE
            WHERE p_iva = $1";

    $params = array($p_iva);
    $result = pg_prepare($db, "riattiva_fornitore", $sql);
    $result = pg_execute($db, "riattiva_fornitore", $params);
    close_pg_connection($db);
    return $result;
}

function aggiorna_fornitore($old_p_iva, $p_iva, $nome, $telefono, $mail, $via, $civico, $citta)
{
    $db = open_pg_connection();

    $sql = "SELECT comicgalaxy.aggiorna_fornitore($1, $2, $3, $4, $5, $6, $7, $8)";
    $params = array($old_p_iva, $p_iva, $nome, $telefono, $mail, $via, $civico, $citta);
    $result = pg_prepare($db, "update_fornitore", $sql);
    $result = pg_execute($db, "update_fornitore", $params);

    if (!$result) {
        $error = pg_last_error($db);
        close_pg_connection($db);
        return $error;
    }

    close_pg_connection($db);
    return true;
}


function crea_fornitore($nome, $p_iva, $mail, $citta, $via, $civico, $telefono)
{
    $db = open_pg_connection();

    $sql = "SELECT comicgalaxy.crea_fornitore($1, $2, $3, $4, $5, $6, $7)";
    $params = array($nome, $p_iva, $mail, $citta, $via, $civico, $telefono);

    $prep = pg_prepare($db, "create_fornitore", $sql);
    if (!$prep) {
        $err = pg_last_error($db);
        close_pg_connection($db);
        return $err;
    }

    $res = pg_execute($db, "create_fornitore", $params);
    if (!$res) {
        $err = pg_last_error($db);
        close_pg_connection($db);
        return $err;
    }

    close_pg_connection($db);
    return true;
}

function getNegozioById($id_negozio)
{
    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.negozio INNER JOIN comicgalaxy.indirizzo ON negozio.id_indirizzo = indirizzo.id
            WHERE negozio.id = $1";

    $param = array($id_negozio);
    $result = pg_prepare($db, "get_negozio_by_id", $sql);
    $result = pg_execute($db, "get_negozio_by_id", $param);
    $negozio = null;
    if ($row = pg_fetch_assoc($result)) {
        $negozio = array(
            'id' => $row['id'],
            'nome' => $row['nome'],
            'data_chiusura' => $row['data_chiusura'],
            'citta' => $row['citta'],
            'telefono' => $row['telefono'],
            'via' => $row['via'],
            'civico' => $row['civico'],
            'manager' => $row['nome_manager'] . ' ' . $row['cognome_manager']
        );
    }

    close_pg_connection($db);
    return $negozio;
}

function aggiorna_negozio($id_negozio, $nome, $telefono, $citta, $via, $civico)
{
    $db = open_pg_connection();
    $sql = "SELECT comicgalaxy.aggiorna_negozio($1, $2, $3, $4, $5, $6)";
    $params = array($id_negozio, $nome, $citta, $via, $civico, $telefono);
    $result = pg_prepare($db, "update_negozio", $sql);
    $result = pg_execute($db, "update_negozio", $params);

    close_pg_connection($db);
    return $result;
}

function cambia_password($mail, $password_attuale, $password_nuova)
{
    $db = open_pg_connection();   // Usa la tua funzione di connessione

    $sql = "SELECT password FROM comicgalaxy.utente WHERE mail = $1";
    $result = pg_query_params($db, $sql, array($mail));

    if (!$result || pg_num_rows($result) == 0) {
        close_pg_connection($db);
        return array("success" => false, "msg" => "Utente non trovato");
    }

    $row = pg_fetch_assoc($result);
    $hash_attuale = $row["password"];

    if (!password_verify($password_attuale, $hash_attuale)) {
        close_pg_connection($db);
        return array("success" => false, "msg" => "Password attuale errata");
    }

    $nuovo_hash = password_hash($password_nuova, PASSWORD_BCRYPT);

    $sql_update = "UPDATE comicgalaxy.utente SET password = $1 WHERE mail = $2";
    $ok = pg_query_params($db, $sql_update, array($nuovo_hash, $mail));

    close_pg_connection($db);

    if ($ok) {
        return array("success" => true, "msg" => "Password aggiornata con successo");
    } else {
        return array("success" => false, "msg" => "Errore durante l'aggiornamento");
    }
}

function aggiorna_telefono($mail, $telefono)
{
    $db = open_pg_connection();

    $sql = "UPDATE comicgalaxy.utente
            SET telefono = $1
            WHERE mail = $2";

    $params = array($telefono, $mail);
    $result = pg_prepare($db, "update_telefono", $sql);
    $result = pg_execute($db, "update_telefono", $params);

    close_pg_connection($db);
    return $result;
}

function getNegozi_aperti()
{
    $db = open_pg_connection();

    $sql = "SELECT n.id, n.nome, i.citta, i.via, i.civico
            FROM comicgalaxy.negozio n
            JOIN comicgalaxy.indirizzo i ON n.id_indirizzo = i.id
            WHERE n.data_chiusura IS NULL
            ORDER BY n.nome";

    $result = pg_prepare($db, "get_negozi_aperti", $sql);
    $result = pg_execute($db, "get_negozi_aperti", array());
    $negozi = array();

    while ($row = pg_fetch_assoc($result)) {
        $negozi[] = array(
            'id' => $row['id'],
            'nome' => $row['nome'],
            'indirizzo' => $row['citta'] . ', ' . $row['via'] . ', ' . $row['civico']
        );
    }

    close_pg_connection($db);
    return $negozi;
}

function getAllProdotti()
{
    $db = open_pg_connection();

    $sql = "SELECT id, nome, descrizione
            FROM comicgalaxy.prodotto
            ORDER BY nome";

    $result = pg_prepare($db, "get_prodotti", $sql);
    $result = pg_execute($db, "get_prodotti", array());

    $prodotti = array();
    while ($row = pg_fetch_assoc($result)) {
        $prodotti[] = $row;
    }
    close_pg_connection($db);
    return $prodotti;
}

function tesseratiNegozio($id_negozio)
{
    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.v_tessere
            WHERE id_negozio = $1";

    $params = array($id_negozio);
    $result = pg_prepare($db, "count_tesserati", $sql);
    $result = pg_execute($db, "count_tesserati", $params);

    $tesserati = array();
    while ($row = pg_fetch_assoc($result)) {
        $stato = "attiva";
        if ($row['sospeso'] === 't' || $row['sospeso'] === true) {
            $stato = "sospesa";
        }
        if ($row['data_scadenza'] < date('Y-m-d')) {
            $stato = "scaduta";
        }
        $tesserati[] = array(
            'cf' => $row['cf'],
            'mail' => $row['mail'],
            'nome' => $row['nome'],
            'cognome' => $row['cognome'],
            'data_emissione' => $row['data_emissione'],
            'data_scadenza' => $row['data_scadenza'],
            'saldo' => $row['saldo'],
            'stato' => $stato
        );
    }

    close_pg_connection($db);
    return $tesserati;
}

function getTesseratiPuntiElevati()
{
    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.v_clienti_punti_elevati
            ORDER BY saldo_punti DESC";

    $result = pg_prepare($db, "get_tesserati_punti_elevati", $sql);
    $result = pg_execute($db, "get_tesserati_punti_elevati", array());

    $tesserati = array();
    while ($row = pg_fetch_assoc($result)) {
        $tesserati[] = array(
            'cf_cliente' => $row['cf_cliente'],
            'mail' => $row['mail'],
            'nome_cliente' => $row['nome_cliente'],
            'cognome_cliente' => $row['cognome_cliente'],
            'punti' => $row['saldo_punti'],
            'nome_negozio' => $row['nome']
        );
    }
    close_pg_connection($db);
    return $tesserati;
}

function ordiniFornitore($p_iva)
{
    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.v_storico_ordini_fornitori
            WHERE p_iva = $1
            ORDER BY data_ordine DESC";

    $param = array($p_iva);
    $result = pg_prepare($db, "get_ordini_fornitore", $sql);
    $result = pg_execute($db, "get_ordini_fornitore", $param);

    $ordini = array();
    while ($row = pg_fetch_assoc($result)) {

        $oggi = date('Y-m-d');
        $dataConsegna = $row['data_consegna'];
        $ritirato = $row['ritirato'];
        if ($ritirato === 't' || $ritirato === true) {
            $stato = "Ritirato";
        } else if ($dataConsegna <= $oggi) {
            $stato = "Da ritirare";
        } else {
            $stato = "In arrivo";
        }

        $ordini[] = array(
            'id_ordine' => $row['id_ordine'],
            'nome_negozio' => $row['nome_negozio'],
            'data_ordine' => $row['data_ordine'],
            'data_consegna' => $row['data_consegna'],
            'prezzo' => $row['prezzo'],
            'totale' => $row['totale'],
            'stato' => $stato
        );
    }
    close_pg_connection($db);
    return $ordini;
}

function sospendi_tessera($cf)
{
    $db = open_pg_connection();

    $sql = "UPDATE comicgalaxy.tessera
            SET sospeso = TRUE
            WHERE cf_cliente = $1";

    $params = array($cf);
    $result = pg_prepare($db, "sospendi_tessera", $sql);
    $result = pg_execute($db, "sospendi_tessera", $params);
    close_pg_connection($db);
    return $result;
}

function riattiva_tessera($cf)
{
    $db = open_pg_connection();

    $sql = "UPDATE comicgalaxy.tessera
            SET sospeso = FALSE
            WHERE cf_cliente = $1";

    $params = array($cf);
    $result = pg_prepare($db, "riattiva_tessera", $sql);
    $result = pg_execute($db, "riattiva_tessera", $params);
    close_pg_connection($db);
    return $result;
}

function getFattureNegozio($id_negozio)
{
    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.v_fatture
            WHERE codice_negozio = $1
            ORDER BY data_acquisto DESC";

    $param = array($id_negozio);
    $result = pg_prepare($db, "get_fatture_negozio", $sql);
    $result = pg_execute($db, "get_fatture_negozio", $param);

    $fatture = array();
    while ($row = pg_fetch_assoc($result)) {
        $fatture[] = $row;
    }
    close_pg_connection($db);
    return $fatture;
}

function getFatturaById($id_fattura)
{
    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.v_fatture 
            WHERE id = $1";

    $param = array($id_fattura);
    $result = pg_prepare($db, "get_fattura_by_id", $sql);
    $result = pg_execute($db, "get_fattura_by_id", $param);

    $fattura = null;
    if ($row = pg_fetch_assoc($result)) {
        $fattura = $row;
    }
    close_pg_connection($db);
    return $fattura;
}

function getProdottiFattura($id_fattura)
{
    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.dettaglio_fattura as d inner join comicgalaxy.prodotto as p on d.id_prodotto = p.id
            WHERE id_fattura = $1";

    $param = array($id_fattura);
    $result = pg_prepare($db, "get_prodotti_fattura", $sql);
    $result = pg_execute($db, "get_prodotti_fattura", $param);

    $prodotti = array();
    while ($row = pg_fetch_assoc($result)) {
        $prodotti[] = $row;
    }
    close_pg_connection($db);
    return $prodotti;
}

function getProdottiNegozio($id_negozio)
{
    $db = open_pg_connection();

    $sql = "SELECT p.id, p.nome, p.descrizione, n.quantita, n.prezzo
            FROM comicgalaxy.prodotto p inner join comicgalaxy.fornitura_negozio n on p.id = n.id_prodotto
            WHERE n.id_negozio = $1
            ORDER BY p.nome";

    $param = array($id_negozio);
    $result = pg_prepare($db, "get_prodotti_negozio", $sql);
    $result = pg_execute($db, "get_prodotti_negozio", $param);

    $prodotti = array();
    while ($row = pg_fetch_assoc($result)) {
        $prodotti[] = $row;
    }

    close_pg_connection($db);
    return $prodotti;
}

function getTessera($mail)
{
    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.v_tessere
            WHERE mail = $1";

    $param = array($mail);
    $result = pg_prepare($db, "get_tessera", $sql);
    $result = pg_execute($db, "get_tessera", $param);

    $tessera = null;
    if ($row = pg_fetch_assoc($result)) {
        $tessera = $row;
    }

    close_pg_connection($db);
    return $tessera;
}

function creaTessera($cf_cliente, $id_negozio)
{
    $db = open_pg_connection();
    var_dump($id_negozio);
    $sql = "SELECT comicgalaxy.crea_tessera($1, $2)";
    $params = array($cf_cliente, $id_negozio);
    $result = pg_prepare($db, "create_tessera", $sql);
    $result = pg_execute($db, "create_tessera", $params);

    if (!$result) {
        $error = pg_last_error($db);

        close_pg_connection($db);
        return $error;
    }

    close_pg_connection($db);
    return true;
}

function rinnova_tessera($cf_cliente)
{
    $db = open_pg_connection();

    $sql = "SELECT comicgalaxy.rinnova_tessera($1)";
    $params = array($cf_cliente);
    $result = pg_prepare($db, "rinnova_tessera", $sql);
    $result = pg_execute($db, "rinnova_tessera", $params);

    if (!$result) {
        $error = pg_last_error($db);

        close_pg_connection($db);
        return $error;
    }

    close_pg_connection($db);
    return true;
}

function esegui_acquisto($cf, $carrello, $id_negozio, $sconto = 0)
{
    $db = open_pg_connection();

    $codici = [];
    $quantita = [];

    foreach ($carrello as $id_prodotto => $item) {
        $codici[] = $id_prodotto;
        $quantita[] = $item['quantita'];
    }

    $codici_pg = '{' . implode(',', $codici) . '}';
    $quantita_pg = '{' . implode(',', $quantita) . '}';

    $sql = "SELECT comicgalaxy.gestisci_acquisto($1::varchar, $2::int, $3::int[], $4::int[], $5::int)";
    $params = array($cf, $id_negozio, $codici_pg, $quantita_pg, $sconto);

    $result = pg_prepare($db, "esegui_acquisto", $sql);
    if (!$result) {
        $error = pg_last_error($db);
        close_pg_connection($db);
        return $error;
    }

    $result = pg_execute($db, "esegui_acquisto", $params);
    if (!$result) {
        $error = pg_last_error($db);
        close_pg_connection($db);
        return $error;
    }

    close_pg_connection($db);

    $row = pg_fetch_row($result);
    if ($row) {
        return true;
    } else {
        return "Errore nell'esecuzione dell'ordine.";
    }
}

function getAcquistiCliente($mail)
{
    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.v_storico_acquisti
            WHERE mail=$1
            ORDER BY data_acquisto DESC";

    $param = array($mail);
    $result = pg_prepare($db, "get_acquisti_cliente", $sql);
    $result = pg_execute($db, "get_acquisti_cliente", $param);

    $acquisti = array();
    while ($row = pg_fetch_assoc($result)) {
        $acquisti[] = $row;
    }
    close_pg_connection($db);
    return $acquisti;
}

function getDettaglioAcquisto($id_fattura)
{
    $db = open_pg_connection();

    $sql = "SELECT *
            FROM comicgalaxy.v_storico_acquisti_dettaglio
            WHERE id_fattura = $1";

    $param = array($id_fattura);
    $result = pg_prepare($db, "get_dettaglio_acquisto", $sql);
    $result = pg_execute($db, "get_dettaglio_acquisto", $param);

    $dettaglio = array();
    while ($row = pg_fetch_assoc($result)) {
        $dettaglio[] = $row;
    }
    close_pg_connection($db);
    return $dettaglio;
}

function salvaOrariNegozio($id_negozio, $orari)
{
    $db = open_pg_connection();

    $sql = "INSERT INTO comicgalaxy.orario(id_negozio, giorno, ora_apertura, ora_chiusura) VALUES ($1, $2, $3, $4)";
    foreach ($orari as $o) {
        $res = pg_query_params($db, $sql, [
            $id_negozio,
            $o['giorno'],
            $o['apertura'],
            $o['chiusura']
        ]);
        if (!$res) {
            $error = pg_last_error($db);
            close_pg_connection($db);
            return $error;
        }
    }

    close_pg_connection($db);
    return true;
}
