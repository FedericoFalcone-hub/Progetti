--
-- PostgreSQL database dump
--

\restrict LHLcsynPeEFs5p7TAlNxaod2TJkhmL0cLnLPJ4XHYpI75aRwDTpO763FHxxB7Xe

-- Dumped from database version 16.11 (Ubuntu 16.11-0ubuntu0.24.04.1)
-- Dumped by pg_dump version 16.11 (Ubuntu 16.11-0ubuntu0.24.04.1)

-- Started on 2025-12-13 11:25:58 CET

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

DROP DATABASE progetto_2425;
--
-- TOC entry 3713 (class 1262 OID 16389)
-- Name: progetto_2425; Type: DATABASE; Schema: -; Owner: postgres
--

CREATE DATABASE progetto_2425 WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'en_US.UTF-8';


ALTER DATABASE progetto_2425 OWNER TO postgres;

\unrestrict LHLcsynPeEFs5p7TAlNxaod2TJkhmL0cLnLPJ4XHYpI75aRwDTpO763FHxxB7Xe
\connect progetto_2425
\restrict LHLcsynPeEFs5p7TAlNxaod2TJkhmL0cLnLPJ4XHYpI75aRwDTpO763FHxxB7Xe

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 6 (class 2615 OID 16632)
-- Name: comicgalaxy; Type: SCHEMA; Schema: -; Owner: federico
--

CREATE SCHEMA comicgalaxy;


ALTER SCHEMA comicgalaxy OWNER TO federico;

--
-- TOC entry 952 (class 1247 OID 16787)
-- Name: giorno_settimana; Type: TYPE; Schema: comicgalaxy; Owner: federico
--

CREATE TYPE comicgalaxy.giorno_settimana AS ENUM (
    'Lunedì',
    'Martedì',
    'Mercoledì',
    'Giovedì',
    'Venerdì',
    'Sabato',
    'Domenica'
);


ALTER TYPE comicgalaxy.giorno_settimana OWNER TO federico;

--
-- TOC entry 957 (class 1247 OID 16839)
-- Name: stato_ordine; Type: TYPE; Schema: comicgalaxy; Owner: federico
--

CREATE TYPE comicgalaxy.stato_ordine AS ENUM (
    'in arrivo',
    'da ritirare',
    'ritirato'
);


ALTER TYPE comicgalaxy.stato_ordine OWNER TO federico;

--
-- TOC entry 960 (class 1247 OID 16847)
-- Name: tipo_prodotto_quantita; Type: TYPE; Schema: comicgalaxy; Owner: federico
--

CREATE TYPE comicgalaxy.tipo_prodotto_quantita AS (
	id_prodotto integer,
	quantita integer
);


ALTER TYPE comicgalaxy.tipo_prodotto_quantita OWNER TO federico;

--
-- TOC entry 263 (class 1255 OID 16665)
-- Name: aggiorna_disponibilita_fornitore(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.aggiorna_disponibilita_fornitore() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare 
	quantita_disponibile integer;
	p_iva varchar;
begin

	select fornitore into p_iva
	from comicgalaxy.ordine
	where new.id_ordine = id;

	select quantita into quantita_disponibile
	from comicgalaxy.fornitura_fornitore
	where new.id_prodotto=id_prodotto and p_iva_fornitore=p_iva;
	
	
	
	if quantita_disponibile<new.quantita then RAISE EXCEPTION 'Quantita richiesta (%s) maggiore della disponibilita (%s)', new.quantita, quantita_disponibile;
	end if;
	
	update comicgalaxy.fornitura_fornitore
	set quantita=quantita-new.quantita
	where id_prodotto=new.id_prodotto and p_iva_fornitore=p_iva;
return new;
end
$$;


ALTER FUNCTION comicgalaxy.aggiorna_disponibilita_fornitore() OWNER TO federico;

--
-- TOC entry 288 (class 1255 OID 17141)
-- Name: aggiorna_fornitore(character varying, character varying, character varying, character varying, character varying, character varying, integer, character varying); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.aggiorna_fornitore(old_iva character varying, p_p_iva character varying, p_nome character varying, p_telefono character varying, p_mail character varying, p_via character varying, p_civico integer, p_citta character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$ 
DECLARE
	f_indirizzo int;
	f_indirizzo_fornitore int;
BEGIN 
	SELECT id into f_indirizzo
	FROM comicgalaxy.indirizzo
	WHERE via=p_via AND civico=p_civico AND citta=p_citta;

	SELECT indirizzo into f_indirizzo_fornitore
	FROM comicgalaxy.fornitore
	WHERE p_iva=old_iva;
	
	if f_indirizzo is not null then
		if f_indirizzo<> f_indirizzo_fornitore then
			RAISE EXCEPTION 'Indirizzo già occupato, % %', f_indirizzo, f_indirizzo_fornitore;
		end if;
	end if;
	
	if f_indirizzo_fornitore is not null then
		UPDATE comicgalaxy.indirizzo set via=p_via, citta=p_citta, civico=p_civico where id=f_indirizzo_fornitore;
	end if;
	if old_iva <> p_p_iva then
		UPDATE comicgalaxy.fornitore SET p_iva=p_p_iva where p_iva=old_iva;
	end if;
	
	UPDATE comicgalaxy.fornitore SET telefono=p_telefono, mail=p_mail, nome=p_nome where p_iva=p_p_iva;
END; 
$$;


ALTER FUNCTION comicgalaxy.aggiorna_fornitore(old_iva character varying, p_p_iva character varying, p_nome character varying, p_telefono character varying, p_mail character varying, p_via character varying, p_civico integer, p_citta character varying) OWNER TO federico;

--
-- TOC entry 289 (class 1255 OID 17152)
-- Name: aggiorna_negozio(integer, character varying, character varying, character varying, integer, character varying); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.aggiorna_negozio(p_id integer, p_nome character varying, p_citta character varying, p_via character varying, p_civico integer, p_telefono character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$ 
DECLARE
	f_indirizzo int;
	f_indirizzo_negozio int;
BEGIN 
	SELECT id into f_indirizzo
	FROM comicgalaxy.indirizzo
	WHERE via=p_via AND civico=p_civico AND citta=p_citta;

	SELECT id_indirizzo into f_indirizzo_negozio
	FROM comicgalaxy.negozio
	WHERE id=p_id;
	
	if f_indirizzo is not null then
		if f_indirizzo<> f_indirizzo_negozio then
			RAISE EXCEPTION 'Indirizzo già occupato';
		end if;
	end if;
	
	UPDATE comicgalaxy.indirizzo set via=p_via, citta=p_citta, civico=p_civico where id=f_indirizzo_negozio;
	
	UPDATE comicgalaxy.negozio SET telefono=p_telefono, nome=p_nome where id=p_id;
END; 
$$;


ALTER FUNCTION comicgalaxy.aggiorna_negozio(p_id integer, p_nome character varying, p_citta character varying, p_via character varying, p_civico integer, p_telefono character varying) OWNER TO federico;

--
-- TOC entry 277 (class 1255 OID 16654)
-- Name: aggiorna_saldo_punti(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.aggiorna_saldo_punti() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare 
	punti_attuali integer;
	punti_guadagnati integer;
	punti_spesi integer;
	scadenza date;
begin
	if exists(select 1 from comicgalaxy.tessera where cf_cliente=new.cf_cliente) then
	select data_scadenza into scadenza
	from comicgalaxy.tessera
	where cf_cliente=new.cf_cliente;

	if scadenza is not null and scadenza > CURRENT_DATE then

		select saldo into punti_attuali
		from comicgalaxy.tessera
		where cf_cliente=new.cf_cliente;

		if new.sconto=5 then
			punti_spesi=100;
		elsif new.sconto=15 then
			punti_spesi=200;
		elsif new.sconto=30 then
			punti_spesi=300;
		else punti_spesi=0;
		end if;

		punti_guadagnati=FLOOR(new.totale)::integer;
		
		update comicgalaxy.tessera
		set saldo=punti_guadagnati+punti_attuali-punti_spesi
		where cf_cliente=new.cf_cliente;
		end if;
end if;
		return new;
end
$$;


ALTER FUNCTION comicgalaxy.aggiorna_saldo_punti() OWNER TO federico;

--
-- TOC entry 287 (class 1255 OID 17121)
-- Name: aggiorna_utente(character, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.aggiorna_utente(p_old_mail character, p_mail character varying, p_nome character varying, p_cognome character varying, p_telefono character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$ 
DECLARE 
f_password varchar; 
f_telefono varchar; 
BEGIN 

	SELECT password, telefono INTO f_password, f_telefono 
	FROM comicgalaxy.utente 
	WHERE mail = p_old_mail; 

	IF p_old_mail <> p_mail then 
		UPDATE comicgalaxy.utente
		SET mail=p_mail
		WHERE mail=p_old_mail;
	END IF;

	IF f_telefono<> p_telefono then 
		UPDATE comicgalaxy.utente 
		SET telefono = p_telefono
		where mail=p_mail; 
	END IF;

	IF EXISTS(SELECT 1 FROM comicgalaxy.cliente c INNER JOIN comicgalaxy.utente u ON c.mail=u.mail) then
		UPDATE comicgalaxy.cliente 
		SET nome=p_nome, cognome=p_cognome 
		WHERE mail=p_mail;
	END IF; 

	IF EXISTS(SELECT 1 FROM comicgalaxy.manager m INNER JOIN comicgalaxy.utente u ON m.mail=u.mail) then
		UPDATE comicgalaxy.manager 
		SET nome=p_nome, cognome=p_cognome 
		WHERE mail=p_mail;
	END IF; 
END; 
$$;


ALTER FUNCTION comicgalaxy.aggiorna_utente(p_old_mail character, p_mail character varying, p_nome character varying, p_cognome character varying, p_telefono character varying) OWNER TO federico;

--
-- TOC entry 275 (class 1255 OID 16722)
-- Name: calcola_sconto(integer, numeric, character varying); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.calcola_sconto(sconto integer, totale numeric, p_cf_cliente character varying) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
DECLARE
    f_valore DECIMAL;
	punti INTEGER;
BEGIN
	SELECT saldo INTO punti
	FROM comicgalaxy.tessera
	WHERE cf_cliente=p_cf_cliente;

	IF sconto = 5 AND punti<100 THEN
		RAISE EXCEPTION 'Punti non sufficienti. Punti disponibili: %', punti;
	ELSIF sconto= 15 AND punti<200 THEN
		RAISE EXCEPTION 'Punti non sufficienti. Punti disponibili: %', punti; 
	ELSIF sconto= 30 AND punti<300 THEN
		RAISE EXCEPTION 'Punti non sufficienti. Punti disponibili: %', punti; 
    END IF;
	
	f_valore=totale*(sconto::numeric /100);

	IF f_valore>100 THEN
		f_valore=100;
	END IF;

    RETURN trunc(f_valore, 2);
END;
$$;


ALTER FUNCTION comicgalaxy.calcola_sconto(sconto integer, totale numeric, p_cf_cliente character varying) OWNER TO federico;

--
-- TOC entry 279 (class 1255 OID 17179)
-- Name: check_codice_fiscale(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.check_codice_fiscale() RETURNS trigger
    LANGUAGE plpgsql
    AS $_$
BEGIN
    IF length(NEW.cf) <> 16 THEN
        RAISE EXCEPTION 'Codice Fiscale non valido: deve avere 16 caratteri';
    END IF;

	 IF NEW.cf !~ '^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$' THEN
        RAISE EXCEPTION 'Codice Fiscale non valido: formato non corretto';
    END IF;

    RETURN NEW;
END;
$_$;


ALTER FUNCTION comicgalaxy.check_codice_fiscale() OWNER TO federico;

--
-- TOC entry 270 (class 1255 OID 17181)
-- Name: check_email_format(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.check_email_format() RETURNS trigger
    LANGUAGE plpgsql
    AS $_$
BEGIN

    IF NEW.mail !~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
        RAISE EXCEPTION 'Email non valida: formato non corretto';
    END IF;

    RETURN NEW;
END;
$_$;


ALTER FUNCTION comicgalaxy.check_email_format() OWNER TO federico;

--
-- TOC entry 269 (class 1255 OID 16782)
-- Name: check_manager_disponibile(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.check_manager_disponibile() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Controlla se il manager è già associato a un altro negozio
    IF EXISTS (
        SELECT 1
        FROM comicgalaxy.negozio
        WHERE manager = NEW.manager and data_chiusura is null
    ) and new.manager <> old.manager THEN
        RAISE EXCEPTION 'Il manager % è già associato ad un altro negozio', NEW.manager;
    END IF;

    RETURN NEW;
END;
$$;


ALTER FUNCTION comicgalaxy.check_manager_disponibile() OWNER TO federico;

--
-- TOC entry 281 (class 1255 OID 17062)
-- Name: check_modifica_data(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.check_modifica_data() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Se la data_consegna viene modificata
    IF NEW.data_consegna <> OLD.data_consegna THEN
        -- Se l'ordine è da ritirare
        IF OLD.ritirato = false AND OLD.data_consegna <= CURRENT_DATE THEN
            RAISE EXCEPTION 'Impossibile modificare la data di un ordine da ritirare';
        END IF;
        -- Controllo aggiuntivo: data_consegna >= data
        IF NEW.data_consegna < NEW.data THEN
            RAISE EXCEPTION 'La data di consegna non può essere precedente alla data dell''ordine';
        END IF;
    END IF;

    RETURN NEW;
END;
$$;


ALTER FUNCTION comicgalaxy.check_modifica_data() OWNER TO federico;

--
-- TOC entry 276 (class 1255 OID 16784)
-- Name: check_orario_apertura_chiusura(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.check_orario_apertura_chiusura() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.ora_apertura >= NEW.ora_chiusura THEN
        RAISE EXCEPTION 'Orario non valido: apertura (%) deve essere minore della chiusura (%)',
            NEW.apertura, NEW.chiusura;
    END IF;

    RETURN NEW;
END;
$$;


ALTER FUNCTION comicgalaxy.check_orario_apertura_chiusura() OWNER TO federico;

--
-- TOC entry 271 (class 1255 OID 17189)
-- Name: check_p_iva(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.check_p_iva() RETURNS trigger
    LANGUAGE plpgsql
    AS $_$
BEGIN
   
    IF NEW.p_iva IS NOT NULL AND NEW.p_iva <> '' THEN

        IF NEW.p_iva !~ '^[A-Z]{2}[0-9]{11}$' THEN
            RAISE EXCEPTION 'Partita IVA non valida: deve iniziare con due lettere (prefisso paese) seguite da 11 cifre';
        END IF;

    END IF;

    RETURN NEW;
END;
$_$;


ALTER FUNCTION comicgalaxy.check_p_iva() OWNER TO federico;

--
-- TOC entry 268 (class 1255 OID 17086)
-- Name: check_password_not_null(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.check_password_not_null() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    
	IF NEW.password IS NULL OR NEW.password = '' then 
		RAISE EXCEPTION 'La password non può essere vuota';
	END IF;
    RETURN NEW;
END;
$$;


ALTER FUNCTION comicgalaxy.check_password_not_null() OWNER TO federico;

--
-- TOC entry 280 (class 1255 OID 17099)
-- Name: check_riapertura_negozio(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.check_riapertura_negozio() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF OLD.data_chiusura IS NOT NULL 
       AND NEW.data_chiusura IS NULL THEN
        RAISE EXCEPTION 'Un negozio chiuso non può essere riaperto.';
    END IF;

    RETURN NEW;
END;
$$;


ALTER FUNCTION comicgalaxy.check_riapertura_negozio() OWNER TO federico;

--
-- TOC entry 272 (class 1255 OID 17183)
-- Name: check_telefono_format(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.check_telefono_format() RETURNS trigger
    LANGUAGE plpgsql
    AS $_$
BEGIN

    IF NEW.telefono IS NOT NULL AND NEW.telefono <> '' THEN

        -- Controllo: +prefisso opzionale (1-3 cifre) seguito da 7-15 cifre
        IF NEW.telefono !~ '^\+?[0-9]{1,3}[0-9]{7,15}$' THEN
            RAISE EXCEPTION 'Numero di telefono non valido: deve contenere prefisso e solo cifre (es. +391234567890)';
        END IF;

    END IF;

    RETURN NEW;
END;
$_$;


ALTER FUNCTION comicgalaxy.check_telefono_format() OWNER TO federico;

--
-- TOC entry 282 (class 1255 OID 17123)
-- Name: crea_cliente(character varying, character varying, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.crea_cliente(p_mail character varying, p_nome character varying, p_cognome character varying, p_telefono character varying, p_password character varying, p_cf character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$ 

BEGIN 
	INSERT INTO comicgalaxy.utente(mail, telefono, password) values (p_mail, p_telefono, p_password);
	INSERT INTO comicgalaxy.cliente(cf, nome, cognome, mail) values (p_cf, p_nome, p_cognome, p_mail);
END; 
$$;


ALTER FUNCTION comicgalaxy.crea_cliente(p_mail character varying, p_nome character varying, p_cognome character varying, p_telefono character varying, p_password character varying, p_cf character varying) OWNER TO federico;

--
-- TOC entry 286 (class 1255 OID 17151)
-- Name: crea_fornitore(character varying, character varying, character varying, character varying, character varying, integer, character varying); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.crea_fornitore(p_nome character varying, p_p_iva character varying, p_mail character varying, p_citta character varying, p_via character varying, p_civico integer, p_telefono character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$ 
DECLARE
	f_id integer;
BEGIN 
	INSERT INTO comicgalaxy.indirizzo(citta, via, civico) values (p_citta, p_via, p_civico)
	RETURNING id INTO f_id;
	INSERT INTO comicgalaxy.fornitore(p_iva,telefono,mail,indirizzo,nome) values (p_p_iva, p_telefono, p_mail, f_id, p_nome);
END; 
$$;


ALTER FUNCTION comicgalaxy.crea_fornitore(p_nome character varying, p_p_iva character varying, p_mail character varying, p_citta character varying, p_via character varying, p_civico integer, p_telefono character varying) OWNER TO federico;

--
-- TOC entry 283 (class 1255 OID 17126)
-- Name: crea_manager(character varying, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.crea_manager(p_mail character varying, p_nome character varying, p_cognome character varying, p_telefono character varying, p_password character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$ 

BEGIN 
	INSERT INTO comicgalaxy.utente(mail, telefono, password) values (p_mail, p_telefono, p_password);
	INSERT INTO comicgalaxy.manager(mail,nome, cognome) values (p_mail,p_nome, p_cognome);
END; 
$$;


ALTER FUNCTION comicgalaxy.crea_manager(p_mail character varying, p_nome character varying, p_cognome character varying, p_telefono character varying, p_password character varying) OWNER TO federico;

--
-- TOC entry 264 (class 1255 OID 17132)
-- Name: crea_negozio(character varying, character varying, character varying, character varying, integer, character varying); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.crea_negozio(p_nome character varying, p_manager character varying, p_citta character varying, p_via character varying, p_civico integer, p_telefono character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$ 
DECLARE
	f_id integer;
BEGIN 
	INSERT INTO comicgalaxy.indirizzo(citta, via, civico) values (p_citta, p_via, p_civico)
	RETURNING id INTO f_id;
	INSERT INTO comicgalaxy.negozio(nome,telefono,manager,id_indirizzo) values (p_nome,p_telefono,p_manager, f_id);
END; 
$$;


ALTER FUNCTION comicgalaxy.crea_negozio(p_nome character varying, p_manager character varying, p_citta character varying, p_via character varying, p_civico integer, p_telefono character varying) OWNER TO federico;

--
-- TOC entry 290 (class 1255 OID 17196)
-- Name: crea_tessera(character varying, integer); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.crea_tessera(p_mail character varying, p_negozio integer) RETURNS void
    LANGUAGE plpgsql
    AS $$ 
DECLARE 
	f_cf varchar;
BEGIN 
	IF EXISTS(SELECT 1 from comicgalaxy.v_tessere where mail=p_mail) then
		raise exception 'Tessera già presente';
	end if;

	select c.cf into f_cf from comicgalaxy.utente u inner join comicgalaxy.cliente c on c.mail=u.mail where u.mail=p_mail;
	if f_cf is null then raise exception 'Cliente non trovato';
	end if;

	insert into comicgalaxy.tessera(cf_cliente,id_negozio,data_emissione, data_scadenza) values (f_cf, p_negozio, CURRENT_DATE, CURRENT_DATE + INTERVAL '1 year');
END; 
$$;


ALTER FUNCTION comicgalaxy.crea_tessera(p_mail character varying, p_negozio integer) OWNER TO federico;

--
-- TOC entry 266 (class 1255 OID 16743)
-- Name: gestisci_acquisto(character varying, integer, integer[], integer[], integer); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.gestisci_acquisto(p_cf_cliente character varying, p_id_negozio integer, p_prodotti integer[], p_quantita integer[], p_sconto integer) RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
    i INTEGER;
    disponibilita_corrente INTEGER;
    prezzo_unitario NUMERIC(10,2);
    totale NUMERIC(10,2) := 0;
    totale_parziale NUMERIC(10,2);
    nuovo_id_fattura INTEGER;
	chiusura date;
BEGIN

	if exists (select 1 from comicgalaxy.negozio where id = p_id_negozio and data_chiusura is not null) then
 		raise exception 'Il negozio % è chiuso', p_id_negozio;
	end if;

	 FOR i IN 1 .. array_length(p_prodotti, 1) LOOP

		SELECT quantita, prezzo INTO disponibilita_corrente, prezzo_unitario
        FROM comicgalaxy.fornitura_negozio
        WHERE id_negozio = p_id_negozio
          AND id_prodotto = p_prodotti[i];
		
		IF disponibilita_corrente < p_quantita[i] THEN
            RAISE EXCEPTION 'Quantità insufficiente per prodotto %', p_prodotti[i];
        END IF;

		IF disponibilita_corrente is null then 
			RAISE EXCEPTION 'Prodotto % non disponibile nel negozio %', p_prodotti[i], p_id_negozio;
        END IF;

		UPDATE comicgalaxy.fornitura_negozio
        SET quantita = quantita - p_quantita[i]
        WHERE id_negozio = p_id_negozio
          AND id_prodotto = p_prodotti[i];

		totale_parziale := prezzo_unitario * p_quantita[i];
        totale := totale + totale_parziale;
    END LOOP;

	totale := totale-comicgalaxy.calcola_sconto(p_sconto, totale, p_cf_cliente);
	
	INSERT INTO comicgalaxy.fattura (data_acquisto, sconto, totale, cf_cliente, codice_negozio)
    VALUES (CURRENT_DATE, p_sconto, totale, p_cf_cliente, p_id_negozio)
    RETURNING id INTO nuovo_id_fattura;

	FOR i IN 1 .. array_length(p_prodotti, 1) LOOP
        SELECT prezzo INTO prezzo_unitario
        FROM comicgalaxy.fornitura_negozio
        WHERE id_negozio = p_id_negozio AND id_prodotto = p_prodotti[i];

        INSERT INTO comicgalaxy.dettaglio_fattura (id_fattura, id_prodotto, prezzo, quantita)
        VALUES (nuovo_id_fattura, p_prodotti[i], prezzo_unitario, p_quantita[i]);
    END LOOP;
END;
$$;


ALTER FUNCTION comicgalaxy.gestisci_acquisto(p_cf_cliente character varying, p_id_negozio integer, p_prodotti integer[], p_quantita integer[], p_sconto integer) OWNER TO federico;

--
-- TOC entry 285 (class 1255 OID 16978)
-- Name: ordina_prodotti(integer, integer[], integer[]); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.ordina_prodotti(p_id_negozio integer, p_prodotti integer[], p_quantita integer[]) RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
    i integer;
    f_fornitore varchar;         -- partita IVA o identificativo fornitore
    f_prezzo numeric;
    f_id_ordine integer;
    f_nome_prodotto varchar;
	f_quantita integer;
    fornitori varchar[] := ARRAY[]::varchar[];  -- inizializzo array vuoto di varchar
    ordini integer[] := ARRAY[]::integer[];     -- inizializzo array vuoto di integer
    idx integer;
    trovato boolean;
    n_prodotti integer;
	q integer;
	mancante integer;
BEGIN
    -- Controlla che gli array abbiano la stessa lunghezza
    IF coalesce(array_length(p_prodotti,1),0) <> coalesce(array_length(p_quantita,1),0) THEN
        RAISE EXCEPTION 'Gli array id_prodotto e quantita devono avere la stessa lunghezza';
    END IF;

    n_prodotti := coalesce(array_length(p_prodotti,1),0);
	i:=1;
	f_quantita=0;
	mancante:=p_quantita[1];
    while i<=n_prodotti LOOP

        trovato := false;
        -- Nome prodotto
        SELECT nome INTO f_nome_prodotto
        FROM comicgalaxy.prodotto
        WHERE id = p_prodotti[i];

        IF f_nome_prodotto IS NULL THEN
            RAISE EXCEPTION 'Prodotto con id % non trovato', p_prodotti[i];
        END IF;

        -- Trova il fornitore più economico con disponibilità
        SELECT p_iva_fornitore, prezzo, quantita
        INTO f_fornitore, f_prezzo, q
        FROM comicgalaxy.fornitura_fornitore fo INNER JOIN comicgalaxy.fornitore f on f.p_iva=fo.p_iva_fornitore
        WHERE id_prodotto = p_prodotti[i] and quantita > 0 and f.sospeso=false
        ORDER BY prezzo ASC
        LIMIT 1;

		if q is not null then
			f_quantita=f_quantita+q;
		end if;

        IF f_fornitore IS NULL THEN
            RAISE EXCEPTION 'Nessun fornitore possiede % unità del prodotto % (id=%)',
                p_quantita[i], f_nome_prodotto, p_prodotti[i];
        END IF;

        -- Cerca se per questo fornitore esiste già un ordine creato in questa chiamata
        IF coalesce(array_length(fornitori,1),0) > 0 THEN
            FOR idx IN 1..array_length(fornitori,1) LOOP
                IF fornitori[idx] = f_fornitore THEN
                    f_id_ordine := ordini[idx];
                    trovato := true;
                    EXIT; -- esco dal for idx
                END IF;
            END LOOP;
        END IF;

        IF NOT trovato THEN
            -- Creo nuovo ordine
            INSERT INTO comicgalaxy.ordine(data_consegna, negozio, fornitore)
            VALUES (CURRENT_DATE + 3, p_id_negozio, f_fornitore)
            RETURNING id INTO f_id_ordine;

            -- memorizzo il fornitore e l'id ordine negli array locali
            fornitori := array_append(fornitori, f_fornitore);
            ordini := array_append(ordini, f_id_ordine);
        END IF;

		if mancante -q > 0 then
			INSERT INTO comicgalaxy.dettaglio_ordini(id_prodotto, id_ordine, quantita, prezzo)
        	VALUES (p_prodotti[i], f_id_ordine, q, f_prezzo);
			mancante:= mancante -q;
		else
			INSERT INTO comicgalaxy.dettaglio_ordini(id_prodotto, id_ordine, quantita, prezzo)
        	VALUES (p_prodotti[i], f_id_ordine, mancante, f_prezzo);
			i:=i+1;
			mancante=p_quantita[1];
		end if;
raise notice 'Mancante %', mancante; 
		
    END LOOP;

END;
$$;


ALTER FUNCTION comicgalaxy.ordina_prodotti(p_id_negozio integer, p_prodotti integer[], p_quantita integer[]) OWNER TO federico;

--
-- TOC entry 265 (class 1255 OID 16708)
-- Name: ordina_prodotto(integer, integer, integer); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.ordina_prodotto(p_id_negozio integer, p_id_prodotto integer, p_quantita integer) RETURNS void
    LANGUAGE plpgsql
    AS $$
declare
	f_fornitore varchar;
	f_prezzo integer;
	f_id_ordine integer;
	f_nome_prodotto varchar;
begin

	select nome into f_nome_prodotto
	from comicgalaxy.prodotto as p
	where p_id_prodotto=p.id;

	select p_iva_fornitore, prezzo into f_fornitore, f_prezzo
	from comicgalaxy.fornitura_fornitore as f
	where f.id_prodotto=p_id_prodotto and f.quantita>=p_quantita
	order by f.prezzo asc
	limit 1;

	if f_fornitore isnull  then
 		raise exception 'Nessun fornitore possiede % unità del prodotto %', p_quantita, f_nome_prodotto;
	end if;
	
	insert into ordine(data_consegna,negozio, fornitore) values
	(CURRENT_DATE + INTERVAL '3 days',p_id_negozio,f_fornitore) 
	returning id into f_id_ordine;

	insert into dettaglio_ordini(id_prodotto, id_ordine,quantita,prezzo) values
	(p_id_prodotto,f_id_ordine,p_quantita,f_prezzo);

end

$$;


ALTER FUNCTION comicgalaxy.ordina_prodotto(p_id_negozio integer, p_id_prodotto integer, p_quantita integer) OWNER TO federico;

--
-- TOC entry 267 (class 1255 OID 16989)
-- Name: riepilogo_prodotto(integer, integer); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.riepilogo_prodotto(p_id_prodotto integer, "p_quantità" integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
DECLARE
    f_quantita integer;
	f_prezzo numeric;
	totale numeric;
	f_quantità_accumulata integer;
BEGIN
	totale:=0;
	f_quantità_accumulata:=0;
    FOR f_quantita, f_prezzo IN
        SELECT quantita, prezzo
        FROM comicgalaxy.fornitura_fornitore
        WHERE id_prodotto = p_id_prodotto
          AND quantita > 0
		ORDER BY prezzo ASC 
    LOOP
        -- Se aggiungendo tutta la quantità del fornitore superiamo la richiesta
		raise notice '%', f_quantita;
        IF f_quantità_accumulata + f_quantita >= p_quantità THEN
            -- aggiungo solo quanto serve
            totale := totale + (p_quantità - f_quantità_accumulata) * f_prezzo;
            f_quantità_accumulata := p_quantità;
            EXIT; -- stop loop
        ELSE
            -- altrimenti aggiungo tutta la quantità del fornitore
            totale := totale + f_quantita * f_prezzo;
            f_quantità_accumulata := f_quantità_accumulata + f_quantita;
        END IF;
    END LOOP;

    RETURN totale;
END;
$$;


ALTER FUNCTION comicgalaxy.riepilogo_prodotto(p_id_prodotto integer, "p_quantità" integer) OWNER TO federico;

--
-- TOC entry 273 (class 1255 OID 17201)
-- Name: rinnova_tessera(character varying); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.rinnova_tessera(p_cf character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
	v_sospeso boolean;
BEGIN

  	 IF NOT EXISTS (
        SELECT 1 FROM comicgalaxy.tessera WHERE cf_cliente = p_cf
    ) THEN
        RAISE EXCEPTION 'Tessera non trovata per il CF: %', p_cf;
    END IF;

	SELECT sospeso INTO v_sospeso
    FROM comicgalaxy.utente u inner join comicgalaxy.cliente c on c.mail=u.mail
    WHERE c.cf = p_cf;

    IF v_sospeso IS NULL THEN
        RAISE EXCEPTION 'Cliente non trovato per il CF: %', p_cf;
    ELSIF v_sospeso = TRUE THEN
        RAISE EXCEPTION 'Impossibile rinnovare la tessera: cliente sospeso (CF: %)', p_cf;
    END IF;

    -- Rinnova la tessera:
    --  - scadenza = oggi + 1 anno
    --  - sospeso = FALSE (se era sospesa)
    UPDATE comicgalaxy.tessera
    SET data_scadenza = CURRENT_DATE + INTERVAL '1 year',
        sospeso  = FALSE
    WHERE cf_cliente = p_cf;

END;
$$;


ALTER FUNCTION comicgalaxy.rinnova_tessera(p_cf character varying) OWNER TO federico;

--
-- TOC entry 278 (class 1255 OID 16728)
-- Name: ritiro_ordine(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.ritiro_ordine() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Per ogni riga dell'ordine, aggiorno o inserisco
     INSERT INTO comicgalaxy.fornitura_negozio (id_negozio, id_prodotto, quantita)
    SELECT NEW.negozio, o.id_prodotto,  o.quantita
    FROM comicgalaxy.dettaglio_ordini o
    WHERE o.id_ordine = NEW.id
    ON CONFLICT (id_negozio, id_prodotto)
    DO UPDATE
      SET quantita = comicgalaxy.fornitura_negozio.quantita + EXCLUDED.quantita;

    RETURN NEW;
END;
$$;


ALTER FUNCTION comicgalaxy.ritiro_ordine() OWNER TO federico;

--
-- TOC entry 274 (class 1255 OID 17197)
-- Name: sospendi_tessera(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.sospendi_tessera() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
	v_cf varchar;
BEGIN

  	 IF NEW.sospeso <> OLD.sospeso THEN

        -- recupero il CF del cliente collegato all'utente tramite mail
        SELECT cf INTO v_cf
        FROM comicgalaxy.cliente
        WHERE mail = NEW.mail;

        -- se esiste il cliente, aggiorno la sua tessera
        IF v_cf IS NOT NULL THEN
            UPDATE comicgalaxy.tessera
            SET sospeso = NEW.sospeso
            WHERE cf_cliente = v_cf;
        END IF;

    END IF;

    RETURN NEW;
END;
$$;


ALTER FUNCTION comicgalaxy.sospendi_tessera() OWNER TO federico;

--
-- TOC entry 284 (class 1255 OID 17134)
-- Name: sospendi_utente(character varying); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.sospendi_utente(p_mail character varying) RETURNS void
    LANGUAGE plpgsql
    AS $_$ 
DECLARE
	id_negozio int;
BEGIN 
	IF EXISTS(SELECT 1 from comicgalaxy.negozio where manager=p_mail) then
		RAISE EXCEPTION 'Non è possibile sospendere un manager associato ad un negozio';
	END IF;
	UPDATE comicgalaxy.utente
            SET sospeso = TRUE
            WHERE mail = $1;
END; 
$_$;


ALTER FUNCTION comicgalaxy.sospendi_utente(p_mail character varying) OWNER TO federico;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 217 (class 1259 OID 16397)
-- Name: cliente; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.cliente (
    cf character(16) NOT NULL,
    nome character varying(50) NOT NULL,
    cognome character varying(50) NOT NULL,
    mail character varying(255) NOT NULL
);


ALTER TABLE comicgalaxy.cliente OWNER TO federico;

--
-- TOC entry 235 (class 1259 OID 16590)
-- Name: dettaglio_fattura; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.dettaglio_fattura (
    id_fattura integer NOT NULL,
    id_prodotto integer NOT NULL,
    prezzo numeric(10,2) NOT NULL,
    quantita integer NOT NULL,
    CONSTRAINT fattura_negozio_prezzo_check CHECK ((prezzo >= (0)::numeric)),
    CONSTRAINT fattura_negozio_quantita_check CHECK ((quantita > 0))
);


ALTER TABLE comicgalaxy.dettaglio_fattura OWNER TO federico;

--
-- TOC entry 233 (class 1259 OID 16556)
-- Name: dettaglio_ordini; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.dettaglio_ordini (
    id_prodotto integer NOT NULL,
    id_ordine integer NOT NULL,
    quantita integer NOT NULL,
    prezzo numeric(10,2) DEFAULT NULL::numeric,
    CONSTRAINT dettaglio_ordini_prezzo_check CHECK ((prezzo >= (0)::numeric)),
    CONSTRAINT dettaglio_ordini_quantita_check CHECK ((quantita > 0))
);


ALTER TABLE comicgalaxy.dettaglio_ordini OWNER TO federico;

--
-- TOC entry 227 (class 1259 OID 16475)
-- Name: fattura; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.fattura (
    id integer NOT NULL,
    data_acquisto date NOT NULL,
    sconto integer DEFAULT 0 NOT NULL,
    totale numeric(10,2) NOT NULL,
    cf_cliente character varying(16) NOT NULL,
    codice_negozio integer NOT NULL,
    CONSTRAINT chk_data_acquisto_non_futura CHECK ((data_acquisto <= CURRENT_DATE)),
    CONSTRAINT fattura_sconto_check CHECK ((((sconto)::numeric >= (0)::numeric) AND ((sconto)::numeric <= (100)::numeric))),
    CONSTRAINT sconto CHECK ((sconto = ANY (ARRAY[0, 5, 15, 30])))
);


ALTER TABLE comicgalaxy.fattura OWNER TO federico;

--
-- TOC entry 226 (class 1259 OID 16474)
-- Name: fattura_id_seq; Type: SEQUENCE; Schema: comicgalaxy; Owner: federico
--

CREATE SEQUENCE comicgalaxy.fattura_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE comicgalaxy.fattura_id_seq OWNER TO federico;

--
-- TOC entry 3714 (class 0 OID 0)
-- Dependencies: 226
-- Name: fattura_id_seq; Type: SEQUENCE OWNED BY; Schema: comicgalaxy; Owner: federico
--

ALTER SEQUENCE comicgalaxy.fattura_id_seq OWNED BY comicgalaxy.fattura.id;


--
-- TOC entry 230 (class 1259 OID 16512)
-- Name: fornitore; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.fornitore (
    p_iva character(13) NOT NULL,
    telefono character varying(20) NOT NULL,
    mail character varying(100) NOT NULL,
    indirizzo integer NOT NULL,
    nome character varying NOT NULL,
    sospeso boolean DEFAULT false NOT NULL
);


ALTER TABLE comicgalaxy.fornitore OWNER TO federico;

--
-- TOC entry 236 (class 1259 OID 16614)
-- Name: fornitura_fornitore; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.fornitura_fornitore (
    p_iva_fornitore character(13) NOT NULL,
    id_prodotto integer NOT NULL,
    prezzo numeric(10,2) NOT NULL,
    quantita integer NOT NULL,
    CONSTRAINT fornitura_fornitore_prezzo_check CHECK ((prezzo >= (0)::numeric)),
    CONSTRAINT fornitura_fornitore_quantita_check CHECK ((quantita >= 0))
);


ALTER TABLE comicgalaxy.fornitura_fornitore OWNER TO federico;

--
-- TOC entry 234 (class 1259 OID 16573)
-- Name: fornitura_negozio; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.fornitura_negozio (
    id_negozio integer NOT NULL,
    id_prodotto integer NOT NULL,
    prezzo numeric(10,2),
    quantita integer NOT NULL,
    CONSTRAINT fornitura_prezzo_check CHECK ((prezzo >= (0)::numeric)),
    CONSTRAINT fornitura_quantita_check CHECK ((quantita >= 0))
);


ALTER TABLE comicgalaxy.fornitura_negozio OWNER TO federico;

--
-- TOC entry 223 (class 1259 OID 16459)
-- Name: indirizzo; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.indirizzo (
    id integer NOT NULL,
    citta character varying(100) NOT NULL,
    via character varying(255) NOT NULL,
    civico integer NOT NULL,
    CONSTRAINT civico_maggiore0 CHECK ((civico > 0))
);


ALTER TABLE comicgalaxy.indirizzo OWNER TO federico;

--
-- TOC entry 222 (class 1259 OID 16458)
-- Name: indirizzo_id_seq; Type: SEQUENCE; Schema: comicgalaxy; Owner: federico
--

CREATE SEQUENCE comicgalaxy.indirizzo_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE comicgalaxy.indirizzo_id_seq OWNER TO federico;

--
-- TOC entry 3715 (class 0 OID 0)
-- Dependencies: 222
-- Name: indirizzo_id_seq; Type: SEQUENCE OWNED BY; Schema: comicgalaxy; Owner: federico
--

ALTER SEQUENCE comicgalaxy.indirizzo_id_seq OWNED BY comicgalaxy.indirizzo.id;


--
-- TOC entry 218 (class 1259 OID 16407)
-- Name: manager; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.manager (
    mail character varying(255) NOT NULL,
    nome character varying(50) NOT NULL,
    cognome character varying(50) NOT NULL
);


ALTER TABLE comicgalaxy.manager OWNER TO federico;

--
-- TOC entry 220 (class 1259 OID 16423)
-- Name: negozio; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.negozio (
    id integer NOT NULL,
    nome character varying(100) NOT NULL,
    telefono character varying(20),
    manager character varying(255) NOT NULL,
    id_indirizzo integer,
    data_chiusura date
);


ALTER TABLE comicgalaxy.negozio OWNER TO federico;

--
-- TOC entry 219 (class 1259 OID 16422)
-- Name: negozio_codice_seq; Type: SEQUENCE; Schema: comicgalaxy; Owner: federico
--

CREATE SEQUENCE comicgalaxy.negozio_codice_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE comicgalaxy.negozio_codice_seq OWNER TO federico;

--
-- TOC entry 3716 (class 0 OID 0)
-- Dependencies: 219
-- Name: negozio_codice_seq; Type: SEQUENCE OWNED BY; Schema: comicgalaxy; Owner: federico
--

ALTER SEQUENCE comicgalaxy.negozio_codice_seq OWNED BY comicgalaxy.negozio.id;


--
-- TOC entry 221 (class 1259 OID 16434)
-- Name: orario; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.orario (
    giorno comicgalaxy.giorno_settimana NOT NULL,
    ora_apertura time without time zone,
    ora_chiusura time without time zone,
    id_negozio integer NOT NULL
);


ALTER TABLE comicgalaxy.orario OWNER TO federico;

--
-- TOC entry 232 (class 1259 OID 16523)
-- Name: ordine; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.ordine (
    id integer NOT NULL,
    data_consegna date,
    negozio integer NOT NULL,
    fornitore character(13) NOT NULL,
    ritirato boolean DEFAULT false NOT NULL,
    data date DEFAULT CURRENT_DATE NOT NULL,
    CONSTRAINT chk_data_consegna CHECK ((data_consegna >= data))
);


ALTER TABLE comicgalaxy.ordine OWNER TO federico;

--
-- TOC entry 231 (class 1259 OID 16522)
-- Name: ordine_id_seq; Type: SEQUENCE; Schema: comicgalaxy; Owner: federico
--

CREATE SEQUENCE comicgalaxy.ordine_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE comicgalaxy.ordine_id_seq OWNER TO federico;

--
-- TOC entry 3717 (class 0 OID 0)
-- Dependencies: 231
-- Name: ordine_id_seq; Type: SEQUENCE OWNED BY; Schema: comicgalaxy; Owner: federico
--

ALTER SEQUENCE comicgalaxy.ordine_id_seq OWNED BY comicgalaxy.ordine.id;


--
-- TOC entry 225 (class 1259 OID 16466)
-- Name: prodotto; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.prodotto (
    id integer NOT NULL,
    nome character varying(100) NOT NULL,
    descrizione text NOT NULL
);


ALTER TABLE comicgalaxy.prodotto OWNER TO federico;

--
-- TOC entry 224 (class 1259 OID 16465)
-- Name: prodotto_id_seq; Type: SEQUENCE; Schema: comicgalaxy; Owner: federico
--

CREATE SEQUENCE comicgalaxy.prodotto_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE comicgalaxy.prodotto_id_seq OWNER TO federico;

--
-- TOC entry 3718 (class 0 OID 0)
-- Dependencies: 224
-- Name: prodotto_id_seq; Type: SEQUENCE OWNED BY; Schema: comicgalaxy; Owner: federico
--

ALTER SEQUENCE comicgalaxy.prodotto_id_seq OWNED BY comicgalaxy.prodotto.id;


--
-- TOC entry 229 (class 1259 OID 16494)
-- Name: tessera; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.tessera (
    id integer NOT NULL,
    saldo integer DEFAULT 0 NOT NULL,
    cf_cliente character(16) NOT NULL,
    id_negozio integer NOT NULL,
    data_scadenza date NOT NULL,
    data_emissione date NOT NULL,
    sospeso boolean DEFAULT false NOT NULL,
    CONSTRAINT chk_emissione_non_futura CHECK ((data_emissione <= CURRENT_DATE)),
    CONSTRAINT tessera_saldo_check CHECK (((saldo)::numeric >= (0)::numeric))
);


ALTER TABLE comicgalaxy.tessera OWNER TO federico;

--
-- TOC entry 228 (class 1259 OID 16493)
-- Name: tessera_id_seq; Type: SEQUENCE; Schema: comicgalaxy; Owner: federico
--

CREATE SEQUENCE comicgalaxy.tessera_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE comicgalaxy.tessera_id_seq OWNER TO federico;

--
-- TOC entry 3719 (class 0 OID 0)
-- Dependencies: 228
-- Name: tessera_id_seq; Type: SEQUENCE OWNED BY; Schema: comicgalaxy; Owner: federico
--

ALTER SEQUENCE comicgalaxy.tessera_id_seq OWNED BY comicgalaxy.tessera.id;


--
-- TOC entry 216 (class 1259 OID 16390)
-- Name: utente; Type: TABLE; Schema: comicgalaxy; Owner: federico
--

CREATE TABLE comicgalaxy.utente (
    mail character varying(255) NOT NULL,
    telefono character varying(20) NOT NULL,
    password character varying(255) NOT NULL,
    sospeso boolean DEFAULT false NOT NULL
);


ALTER TABLE comicgalaxy.utente OWNER TO federico;

--
-- TOC entry 243 (class 1259 OID 17073)
-- Name: v_clienti; Type: VIEW; Schema: comicgalaxy; Owner: federico
--

CREATE VIEW comicgalaxy.v_clienti AS
 SELECT t.id_negozio,
    n.nome AS nome_negozio,
    t.cf_cliente,
    c.nome,
    c.cognome,
    c.mail,
    u.telefono,
    t.saldo,
    t.data_emissione,
    t.data_scadenza,
    u.sospeso
   FROM (((comicgalaxy.tessera t
     JOIN comicgalaxy.negozio n ON ((n.id = t.id_negozio)))
     JOIN comicgalaxy.cliente c ON ((t.cf_cliente = c.cf)))
     JOIN comicgalaxy.utente u ON (((u.mail)::text = (c.mail)::text)));


ALTER VIEW comicgalaxy.v_clienti OWNER TO federico;

--
-- TOC entry 244 (class 1259 OID 17158)
-- Name: v_clienti_punti_elevati; Type: VIEW; Schema: comicgalaxy; Owner: federico
--

CREATE VIEW comicgalaxy.v_clienti_punti_elevati AS
 SELECT c.cf AS cf_cliente,
    c.nome AS nome_cliente,
    c.cognome AS cognome_cliente,
    c.mail,
    t.id_negozio,
    n.nome,
    t.id AS id_tessera,
    t.data_emissione,
    t.saldo AS saldo_punti
   FROM ((comicgalaxy.cliente c
     JOIN comicgalaxy.tessera t ON ((c.cf = t.cf_cliente)))
     JOIN comicgalaxy.negozio n ON ((n.id = t.id_negozio)))
  WHERE (t.saldo > 300);


ALTER VIEW comicgalaxy.v_clienti_punti_elevati OWNER TO federico;

--
-- TOC entry 245 (class 1259 OID 17174)
-- Name: v_fatture; Type: VIEW; Schema: comicgalaxy; Owner: federico
--

CREATE VIEW comicgalaxy.v_fatture AS
 SELECT f.id,
    f.data_acquisto,
    f.sconto,
    f.totale,
    f.cf_cliente,
    f.codice_negozio,
    c.nome,
    c.cognome
   FROM (comicgalaxy.fattura f
     JOIN comicgalaxy.cliente c ON ((c.cf = (f.cf_cliente)::bpchar)));


ALTER VIEW comicgalaxy.v_fatture OWNER TO federico;

--
-- TOC entry 240 (class 1259 OID 17029)
-- Name: v_prodotti_ordinabili; Type: VIEW; Schema: comicgalaxy; Owner: federico
--

CREATE VIEW comicgalaxy.v_prodotti_ordinabili AS
 SELECT p.id,
    p.nome AS nome_prodotto,
    trunc(avg(fo.prezzo), 2) AS prezzo_medio,
    sum(fo.quantita) AS quantita_totale
   FROM ((comicgalaxy.prodotto p
     JOIN comicgalaxy.fornitura_fornitore fo ON ((fo.id_prodotto = p.id)))
     JOIN comicgalaxy.fornitore f ON ((fo.p_iva_fornitore = f.p_iva)))
  GROUP BY p.id, p.nome, f.sospeso
 HAVING ((sum(fo.quantita) > 0) AND (f.sospeso = false))
  ORDER BY p.nome;


ALTER VIEW comicgalaxy.v_prodotti_ordinabili OWNER TO federico;

--
-- TOC entry 246 (class 1259 OID 17218)
-- Name: v_storico_acquisti; Type: VIEW; Schema: comicgalaxy; Owner: federico
--

CREATE VIEW comicgalaxy.v_storico_acquisti AS
 SELECT u.mail,
    c.nome,
    c.cognome,
    c.cf,
    n.nome AS nome_negozio,
    f.data_acquisto,
    f.sconto,
    f.totale,
    f.id
   FROM (((comicgalaxy.cliente c
     JOIN comicgalaxy.fattura f ON ((c.cf = (f.cf_cliente)::bpchar)))
     JOIN comicgalaxy.utente u ON (((u.mail)::text = (c.mail)::text)))
     JOIN comicgalaxy.negozio n ON ((n.id = f.codice_negozio)));


ALTER VIEW comicgalaxy.v_storico_acquisti OWNER TO federico;

--
-- TOC entry 247 (class 1259 OID 17223)
-- Name: v_storico_acquisti_dettaglio; Type: VIEW; Schema: comicgalaxy; Owner: federico
--

CREATE VIEW comicgalaxy.v_storico_acquisti_dettaglio AS
 SELECT df.id_fattura,
    df.id_prodotto,
    df.prezzo,
    df.quantita,
    p.nome AS nome_prodotto
   FROM (comicgalaxy.dettaglio_fattura df
     JOIN comicgalaxy.prodotto p ON ((df.id_prodotto = p.id)));


ALTER VIEW comicgalaxy.v_storico_acquisti_dettaglio OWNER TO federico;

--
-- TOC entry 242 (class 1259 OID 17052)
-- Name: v_storico_ordini_fornitori; Type: VIEW; Schema: comicgalaxy; Owner: federico
--

CREATE VIEW comicgalaxy.v_storico_ordini_fornitori AS
SELECT
    NULL::character(13) AS p_iva,
    NULL::character varying AS nome,
    NULL::integer AS id_ordine,
    NULL::integer AS id_negozio,
    NULL::character varying(100) AS nome_negozio,
    NULL::date AS data_ordine,
    NULL::date AS data_consegna,
    NULL::boolean AS ritirato,
    NULL::numeric AS totale;


ALTER VIEW comicgalaxy.v_storico_ordini_fornitori OWNER TO federico;

--
-- TOC entry 241 (class 1259 OID 17044)
-- Name: v_storico_prodotti_ordine; Type: VIEW; Schema: comicgalaxy; Owner: federico
--

CREATE VIEW comicgalaxy.v_storico_prodotti_ordine AS
 SELECT o.id,
    p.nome AS nome_prodotto,
    f.nome AS nome_fornitore,
    d.quantita,
    d.prezzo,
    ((d.quantita)::numeric * d.prezzo) AS totale
   FROM (((comicgalaxy.ordine o
     JOIN comicgalaxy.dettaglio_ordini d ON ((d.id_ordine = o.id)))
     JOIN comicgalaxy.fornitore f ON ((f.p_iva = o.fornitore)))
     JOIN comicgalaxy.prodotto p ON ((p.id = d.id_prodotto)))
  GROUP BY o.id, p.nome, f.nome, d.quantita, d.prezzo;


ALTER VIEW comicgalaxy.v_storico_prodotti_ordine OWNER TO federico;

--
-- TOC entry 237 (class 1259 OID 16761)
-- Name: v_storico_tessere; Type: VIEW; Schema: comicgalaxy; Owner: federico
--

CREATE VIEW comicgalaxy.v_storico_tessere AS
 SELECT t.id_negozio,
    n.nome AS nome_negozio,
    t.cf_cliente,
    t.saldo,
    t.data_emissione,
    t.data_scadenza
   FROM (comicgalaxy.tessera t
     JOIN comicgalaxy.negozio n ON ((n.id = t.id_negozio)))
  WHERE (n.data_chiusura IS NOT NULL);


ALTER VIEW comicgalaxy.v_storico_tessere OWNER TO federico;

--
-- TOC entry 248 (class 1259 OID 17227)
-- Name: v_tessere; Type: VIEW; Schema: comicgalaxy; Owner: federico
--

CREATE VIEW comicgalaxy.v_tessere AS
 SELECT t.id,
    t.id_negozio,
    n.nome AS nome_negozio,
    c.nome,
    c.cognome,
    c.mail,
    c.cf,
    t.saldo,
    t.data_emissione,
    t.data_scadenza,
    t.sospeso
   FROM ((comicgalaxy.tessera t
     JOIN comicgalaxy.negozio n ON ((n.id = t.id_negozio)))
     JOIN comicgalaxy.cliente c ON ((t.cf_cliente = c.cf)));


ALTER VIEW comicgalaxy.v_tessere OWNER TO federico;

--
-- TOC entry 3435 (class 2604 OID 16478)
-- Name: fattura id; Type: DEFAULT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fattura ALTER COLUMN id SET DEFAULT nextval('comicgalaxy.fattura_id_seq'::regclass);


--
-- TOC entry 3433 (class 2604 OID 16462)
-- Name: indirizzo id; Type: DEFAULT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.indirizzo ALTER COLUMN id SET DEFAULT nextval('comicgalaxy.indirizzo_id_seq'::regclass);


--
-- TOC entry 3432 (class 2604 OID 16426)
-- Name: negozio id; Type: DEFAULT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.negozio ALTER COLUMN id SET DEFAULT nextval('comicgalaxy.negozio_codice_seq'::regclass);


--
-- TOC entry 3441 (class 2604 OID 16526)
-- Name: ordine id; Type: DEFAULT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.ordine ALTER COLUMN id SET DEFAULT nextval('comicgalaxy.ordine_id_seq'::regclass);


--
-- TOC entry 3434 (class 2604 OID 16469)
-- Name: prodotto id; Type: DEFAULT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.prodotto ALTER COLUMN id SET DEFAULT nextval('comicgalaxy.prodotto_id_seq'::regclass);


--
-- TOC entry 3437 (class 2604 OID 16497)
-- Name: tessera id; Type: DEFAULT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.tessera ALTER COLUMN id SET DEFAULT nextval('comicgalaxy.tessera_id_seq'::regclass);


--
-- TOC entry 3688 (class 0 OID 16397)
-- Dependencies: 217
-- Data for Name: cliente; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.cliente VALUES ('FRRDVD93D10M082U', 'Davide', 'Ferri', 'davide.ferri@example.it');
INSERT INTO comicgalaxy.cliente VALUES ('MNTLNE90E62H501Q', 'Elena', 'Monti', 'elena.monti@example.it');
INSERT INTO comicgalaxy.cliente VALUES ('RSSALC99A41F205X', 'Alice', 'Rossi', 'alice.rossi@example.it');
INSERT INTO comicgalaxy.cliente VALUES ('BNCBRN85B12M501Y', 'Brunos', 'Bianchi', 'bruno.bianchi@example.it');
INSERT INTO comicgalaxy.cliente VALUES ('RSSMRC85M01H501U', 'Marco', 'Rossi', 'marco.rossi@example.it');
INSERT INTO comicgalaxy.cliente VALUES ('a               ', 'Francesco', 'Bernoulli', 'francesco.bernoulli@gmail.com');
INSERT INTO comicgalaxy.cliente VALUES ('VRDCRL01C55L219T', 'francos', 'franchinis', 'carlas.verdi@example.it');
INSERT INTO comicgalaxy.cliente VALUES ('FLCFRC98C12F205D', 'Federico', 'Falcone', 'federico.falcone@cliente.it');


--
-- TOC entry 3706 (class 0 OID 16590)
-- Dependencies: 235
-- Data for Name: dettaglio_fattura; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.dettaglio_fattura VALUES (73, 48, 4.00, 2);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (74, 48, 4.00, 2);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (75, 48, 4.00, 2);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (76, 48, 4.00, 2);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (77, 48, 4.00, 2);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (78, 48, 4.00, 3);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (78, 15, 9.90, 3);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (79, 48, 4.00, 4);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (80, 48, 4.00, 4);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (81, 48, 4.00, 4);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (82, 48, 4.00, 4);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (83, 48, 4.00, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (84, 48, 4.00, 2);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (85, 15, 9.90, 4);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (86, 15, 9.90, 3);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (87, 15, 9.90, 3);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (88, 48, 4.00, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (89, 48, 4.00, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (90, 48, 4.00, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (91, 48, 4.00, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (92, 48, 4.00, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (93, 48, 4.00, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (94, 48, 4.00, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (95, 48, 4.00, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (96, 1, 1.00, 5);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (97, 1, 1.00, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (98, 1, 1.00, 4);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (99, 1, 1.00, 4);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (105, 48, 4.00, 4);


--
-- TOC entry 3704 (class 0 OID 16556)
-- Dependencies: 233
-- Data for Name: dettaglio_ordini; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.dettaglio_ordini VALUES (1, 1, 3, 1.00);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (1, 2, 3, 1.00);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (1, 3, 10, 1.00);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (47, 3, 10, 4.99);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (1, 4, 10, 1.00);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (47, 4, 10, 4.99);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (47, 5, 4, 4.99);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (1, 6, 1, 1.00);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (47, 7, 1, 4.99);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (15, 8, 200, 8.99);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (15, 9, 50, 8.99);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (1, 10, 50, 2.00);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (1, 11, 50, 2.00);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (1, 12, 57, 2.00);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (1, 13, 20, 1.00);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (1, 14, 1, 1.00);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (48, 15, 100, 3.00);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (1, 16, 449, 1.00);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (1, 17, 51, 2.00);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (48, 18, 2, 3.00);
INSERT INTO comicgalaxy.dettaglio_ordini VALUES (48, 19, 2, 3.00);


--
-- TOC entry 3698 (class 0 OID 16475)
-- Dependencies: 227
-- Data for Name: fattura; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.fattura VALUES (73, '2025-12-10', 0, 8.00, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (74, '2025-12-10', 0, 8.00, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (75, '2025-12-10', 0, 8.00, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (76, '2025-12-10', 0, 8.00, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (77, '2025-12-10', 0, 8.00, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (78, '2025-12-10', 0, 41.70, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (79, '2025-12-10', 0, 16.00, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (80, '2025-12-10', 0, 16.00, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (81, '2025-12-10', 0, 16.00, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (82, '2025-12-10', 0, 16.00, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (83, '2025-12-10', 5, 3.80, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (84, '2025-12-10', 5, 7.60, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (85, '2025-12-10', 5, 37.62, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (86, '2025-12-11', 5, 28.22, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (87, '2025-12-12', 0, 29.70, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (88, '2025-12-12', 0, 4.00, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (89, '2025-12-12', 0, 4.00, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (90, '2025-12-12', 5, 3.80, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (91, '2025-12-12', 5, 3.80, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (92, '2025-12-12', 5, 3.80, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (93, '2025-12-12', 5, 3.80, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (94, '2025-12-12', 5, 3.80, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (95, '2025-12-12', 5, 3.80, 'FLCFRC98C12F205D', 1);
INSERT INTO comicgalaxy.fattura VALUES (96, '2025-12-13', 0, 5.00, 'FLCFRC98C12F205D', 3);
INSERT INTO comicgalaxy.fattura VALUES (97, '2025-12-13', 0, 1.00, 'FLCFRC98C12F205D', 3);
INSERT INTO comicgalaxy.fattura VALUES (98, '2025-12-13', 5, 3.80, 'FLCFRC98C12F205D', 3);
INSERT INTO comicgalaxy.fattura VALUES (99, '2025-12-13', 5, 3.80, 'FLCFRC98C12F205D', 3);
INSERT INTO comicgalaxy.fattura VALUES (105, '2025-12-13', 0, 16.00, 'MNTLNE90E62H501Q', 1);


--
-- TOC entry 3701 (class 0 OID 16512)
-- Dependencies: 230
-- Data for Name: fornitore; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.fornitore VALUES ('IT04567891234', '+39 06 1234 5678', 'contatti@fumettieco.it', 34, 'Fumetti & Co. S.r.l.', false);
INSERT INTO comicgalaxy.fornitore VALUES ('IT45678901234', '339 251 8746', 'fornitore.roma@example.com', 6, 'LazioTrade Srl', false);
INSERT INTO comicgalaxy.fornitore VALUES ('IT23456789012', '366 794 3201', 'fornitore.torino@example.com', 7, 'PiemonteDistribuzione', false);
INSERT INTO comicgalaxy.fornitore VALUES ('IT12345678901', '347 582 9130', 'fornitore.firenze@example.com', 38, 'ArnoTrade Srl', false);
INSERT INTO comicgalaxy.fornitore VALUES ('IT34567890123', '328 476 1095', 'fornitore.bologna@example.com', 37, 'Emilia Comics Network', false);


--
-- TOC entry 3707 (class 0 OID 16614)
-- Dependencies: 236
-- Data for Name: fornitura_fornitore; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT23456789012', 15, 8.99, 9584);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT12345678901', 47, 4.99, 875);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT12345678901', 49, 3.99, 0);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT23456789012', 3, 2.00, 0);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT23456789012', 50, 3.00, 0);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT12345678901', 50, 5.00, 0);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT12345678901', 1, 1.00, 0);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT34567890123', 4, 2.00, 0);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT34567890123', 2, 2.00, 0);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT23456789012', 1, 2.00, 291);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT34567890123', 48, 3.00, 12288);


--
-- TOC entry 3705 (class 0 OID 16573)
-- Dependencies: 234
-- Data for Name: fornitura_negozio; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.fornitura_negozio VALUES (1, 48, 4.00, 60);
INSERT INTO comicgalaxy.fornitura_negozio VALUES (1, 2, 7.49, 30);
INSERT INTO comicgalaxy.fornitura_negozio VALUES (1, 5, 8.75, 40);
INSERT INTO comicgalaxy.fornitura_negozio VALUES (1, 1, 5.00, 777);
INSERT INTO comicgalaxy.fornitura_negozio VALUES (1, 15, 9.90, 237);
INSERT INTO comicgalaxy.fornitura_negozio VALUES (1, 47, 4.99, 1);
INSERT INTO comicgalaxy.fornitura_negozio VALUES (1, 23, 8.78, 5);
INSERT INTO comicgalaxy.fornitura_negozio VALUES (3, 1, 1.00, 9586);


--
-- TOC entry 3694 (class 0 OID 16459)
-- Dependencies: 223
-- Data for Name: indirizzo; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.indirizzo VALUES (38, 'Firenze', 'Via del Commercio', 26);
INSERT INTO comicgalaxy.indirizzo VALUES (1, 'Milano', 'Via Dante Alighieri', 12);
INSERT INTO comicgalaxy.indirizzo VALUES (2, 'Roma', 'Via Appia Nuova', 45);
INSERT INTO comicgalaxy.indirizzo VALUES (3, 'Torino', 'Corso Vittorio Emanuele II', 98);
INSERT INTO comicgalaxy.indirizzo VALUES (4, 'Firenze', 'Via dei Calzaiuoli', 27);
INSERT INTO comicgalaxy.indirizzo VALUES (6, 'Roma', 'Via dei Fornitori', 12);
INSERT INTO comicgalaxy.indirizzo VALUES (7, 'Torino', 'Corso Fiume', 45);
INSERT INTO comicgalaxy.indirizzo VALUES (18, 'Padova', 'Via Solferino', 15);
INSERT INTO comicgalaxy.indirizzo VALUES (19, 'Napoli', 'Via Toledo', 40);
INSERT INTO comicgalaxy.indirizzo VALUES (5, 'Bologna', 'Via Indipendenza', 66);
INSERT INTO comicgalaxy.indirizzo VALUES (10, 'Bologna', 'Via delle Camelie', 27);
INSERT INTO comicgalaxy.indirizzo VALUES (34, 'Roma', 'Via dei Fumettisti', 12);
INSERT INTO comicgalaxy.indirizzo VALUES (39, 'Catania', 'Via Generale Marivigna', 12);
INSERT INTO comicgalaxy.indirizzo VALUES (37, 'Bologna', 'Via delle Camelie', 28);


--
-- TOC entry 3689 (class 0 OID 16407)
-- Dependencies: 218
-- Data for Name: manager; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.manager VALUES ('federico.falcone@comicgalaxy.it', 'Federico', 'Falcone');
INSERT INTO comicgalaxy.manager VALUES ('luca.rossi@comicgalaxy.it', 'Luca', 'Rossi');
INSERT INTO comicgalaxy.manager VALUES ('elena.colombo@comicgalaxy.it', 'Elena', 'Colombo');
INSERT INTO comicgalaxy.manager VALUES ('gennaro.esposito@comicgalaxy.it', 'Gennaro', 'Esposito');
INSERT INTO comicgalaxy.manager VALUES ('giulia.luna@comicgalaxy.it', 'Giulia', 'Luna');
INSERT INTO comicgalaxy.manager VALUES ('hugo.marini@comicgalaxy.it', 'Hugo', 'Marini');
INSERT INTO comicgalaxy.manager VALUES ('irene.sarti@comicgalaxy.it', 'Irene', 'Sarti');
INSERT INTO comicgalaxy.manager VALUES ('fabio.gallo@comicgalaxy.it', 'Fabio', 'Gallo');
INSERT INTO comicgalaxy.manager VALUES ('luca.moretti@comicgalaxy.it', 'Luca', 'Moretti');


--
-- TOC entry 3691 (class 0 OID 16423)
-- Dependencies: 220
-- Data for Name: negozio; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.negozio VALUES (1, 'ComicGalaxy Milano', '5839593092', 'federico.falcone@comicgalaxy.it', 1, NULL);
INSERT INTO comicgalaxy.negozio VALUES (15, 'ComicGalaxy Bologna', '0123456789', 'elena.colombo@comicgalaxy.it', 10, '2025-11-25');
INSERT INTO comicgalaxy.negozio VALUES (23, 'ComicGalaxy Napoli', '0123456789', 'gennaro.esposito@comicgalaxy.it', 19, NULL);
INSERT INTO comicgalaxy.negozio VALUES (2, 'ComicGalaxy Roma', '0612345678', 'giulia.luna@comicgalaxy.it', 2, '2025-10-11');
INSERT INTO comicgalaxy.negozio VALUES (22, 'ComicGalaxy Padova', '0123456789', 'luca.rossi@comicgalaxy.it', 18, NULL);
INSERT INTO comicgalaxy.negozio VALUES (3, 'ComicGalaxy Torino', '0118765432', 'hugo.marini@comicgalaxy.it', 3, NULL);
INSERT INTO comicgalaxy.negozio VALUES (4, 'ComicGalaxy Firenze', '0553344556', 'irene.sarti@comicgalaxy.it', 4, NULL);
INSERT INTO comicgalaxy.negozio VALUES (5, 'ComicGalaxy Bologna', '0516677889', 'luca.moretti@comicgalaxy.it', 5, NULL);
INSERT INTO comicgalaxy.negozio VALUES (24, 'ComicGalaxy Catania', '0123456789', 'fabio.gallo@comicgalaxy.it', 39, '2025-12-12');


--
-- TOC entry 3692 (class 0 OID 16434)
-- Dependencies: 221
-- Data for Name: orario; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.orario VALUES ('Lunedì', '10:00:00', '19:00:00', 2);
INSERT INTO comicgalaxy.orario VALUES ('Martedì', '10:00:00', '19:00:00', 2);
INSERT INTO comicgalaxy.orario VALUES ('Mercoledì', '10:00:00', '19:00:00', 2);
INSERT INTO comicgalaxy.orario VALUES ('Giovedì', '10:00:00', '19:00:00', 2);
INSERT INTO comicgalaxy.orario VALUES ('Venerdì', '10:00:00', '19:00:00', 2);
INSERT INTO comicgalaxy.orario VALUES ('Sabato', '10:00:00', '17:00:00', 2);
INSERT INTO comicgalaxy.orario VALUES ('Domenica', '11:00:00', '15:00:00', 2);
INSERT INTO comicgalaxy.orario VALUES ('Lunedì', '09:30:00', '18:30:00', 3);
INSERT INTO comicgalaxy.orario VALUES ('Martedì', '09:30:00', '18:30:00', 3);
INSERT INTO comicgalaxy.orario VALUES ('Mercoledì', '09:30:00', '18:30:00', 3);
INSERT INTO comicgalaxy.orario VALUES ('Giovedì', '09:30:00', '18:30:00', 3);
INSERT INTO comicgalaxy.orario VALUES ('Venerdì', '09:30:00', '18:30:00', 3);
INSERT INTO comicgalaxy.orario VALUES ('Sabato', '10:00:00', '16:00:00', 3);
INSERT INTO comicgalaxy.orario VALUES ('Domenica', NULL, NULL, 3);
INSERT INTO comicgalaxy.orario VALUES ('Lunedì', '08:00:00', '17:00:00', 4);
INSERT INTO comicgalaxy.orario VALUES ('Martedì', '08:00:00', '17:00:00', 4);
INSERT INTO comicgalaxy.orario VALUES ('Mercoledì', '08:00:00', '17:00:00', 4);
INSERT INTO comicgalaxy.orario VALUES ('Giovedì', '08:00:00', '17:00:00', 4);
INSERT INTO comicgalaxy.orario VALUES ('Venerdì', '08:00:00', '17:00:00', 4);
INSERT INTO comicgalaxy.orario VALUES ('Sabato', '09:00:00', '14:00:00', 4);
INSERT INTO comicgalaxy.orario VALUES ('Domenica', NULL, NULL, 4);
INSERT INTO comicgalaxy.orario VALUES ('Lunedì', '10:00:00', '20:00:00', 5);
INSERT INTO comicgalaxy.orario VALUES ('Martedì', '10:00:00', '20:00:00', 5);
INSERT INTO comicgalaxy.orario VALUES ('Mercoledì', '10:00:00', '20:00:00', 5);
INSERT INTO comicgalaxy.orario VALUES ('Giovedì', '10:00:00', '20:00:00', 5);
INSERT INTO comicgalaxy.orario VALUES ('Venerdì', '10:00:00', '20:00:00', 5);
INSERT INTO comicgalaxy.orario VALUES ('Sabato', '11:00:00', '18:00:00', 5);
INSERT INTO comicgalaxy.orario VALUES ('Domenica', NULL, NULL, 5);
INSERT INTO comicgalaxy.orario VALUES ('Lunedì', '11:00:00', '18:00:00', 1);
INSERT INTO comicgalaxy.orario VALUES ('Martedì', '09:00:00', '18:00:00', 1);
INSERT INTO comicgalaxy.orario VALUES ('Mercoledì', '09:00:00', '18:00:00', 1);
INSERT INTO comicgalaxy.orario VALUES ('Giovedì', '09:00:00', '18:00:00', 1);
INSERT INTO comicgalaxy.orario VALUES ('Venerdì', '09:00:00', '18:00:00', 1);
INSERT INTO comicgalaxy.orario VALUES ('Sabato', '10:00:00', '15:00:00', 1);
INSERT INTO comicgalaxy.orario VALUES ('Domenica', NULL, NULL, 1);
INSERT INTO comicgalaxy.orario VALUES ('Lunedì', '08:00:00', '20:00:00', 23);
INSERT INTO comicgalaxy.orario VALUES ('Martedì', NULL, NULL, 23);
INSERT INTO comicgalaxy.orario VALUES ('Mercoledì', NULL, NULL, 23);
INSERT INTO comicgalaxy.orario VALUES ('Giovedì', NULL, NULL, 23);
INSERT INTO comicgalaxy.orario VALUES ('Venerdì', NULL, NULL, 23);
INSERT INTO comicgalaxy.orario VALUES ('Sabato', NULL, NULL, 23);
INSERT INTO comicgalaxy.orario VALUES ('Domenica', NULL, NULL, 23);


--
-- TOC entry 3703 (class 0 OID 16523)
-- Dependencies: 232
-- Data for Name: ordine; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.ordine VALUES (1, '2025-11-22', 1, 'IT12345678901', true, '2025-11-22');
INSERT INTO comicgalaxy.ordine VALUES (3, '2025-11-22', 1, 'IT12345678901', true, '2025-11-22');
INSERT INTO comicgalaxy.ordine VALUES (4, '2025-11-22', 1, 'IT12345678901', true, '2025-11-22');
INSERT INTO comicgalaxy.ordine VALUES (5, '2025-11-23', 1, 'IT12345678901', true, '2025-11-23');
INSERT INTO comicgalaxy.ordine VALUES (2, '2025-11-22', 1, 'IT12345678901', true, '2025-11-22');
INSERT INTO comicgalaxy.ordine VALUES (6, '2025-11-23', 1, 'IT12345678901', true, '2025-11-23');
INSERT INTO comicgalaxy.ordine VALUES (7, '2025-11-23', 1, 'IT12345678901', true, '2025-11-23');
INSERT INTO comicgalaxy.ordine VALUES (8, '2025-11-23', 1, 'IT23456789012', true, '2025-11-23');
INSERT INTO comicgalaxy.ordine VALUES (11, '2025-12-05', 1, 'IT23456789012', true, '2025-12-02');
INSERT INTO comicgalaxy.ordine VALUES (9, '2025-11-27', 1, 'IT23456789012', true, '2025-11-27');
INSERT INTO comicgalaxy.ordine VALUES (12, '2025-12-05', 1, 'IT23456789012', true, '2025-12-02');
INSERT INTO comicgalaxy.ordine VALUES (10, '2025-12-05', 1, 'IT23456789012', true, '2025-12-02');
INSERT INTO comicgalaxy.ordine VALUES (13, '2025-12-05', 1, 'IT12345678901', true, '2025-12-05');
INSERT INTO comicgalaxy.ordine VALUES (14, '2025-12-05', 1, 'IT12345678901', true, '2025-12-05');
INSERT INTO comicgalaxy.ordine VALUES (15, '2025-12-05', 1, 'IT34567890123', true, '2025-12-05');
INSERT INTO comicgalaxy.ordine VALUES (18, '2025-12-08', 1, 'IT34567890123', true, '2025-12-08');
INSERT INTO comicgalaxy.ordine VALUES (17, '2025-12-10', 1, 'IT23456789012', true, '2025-12-07');
INSERT INTO comicgalaxy.ordine VALUES (19, '2025-12-11', 1, 'IT34567890123', true, '2025-12-08');
INSERT INTO comicgalaxy.ordine VALUES (16, '2025-12-10', 1, 'IT12345678901', true, '2025-12-07');


--
-- TOC entry 3696 (class 0 OID 16466)
-- Dependencies: 225
-- Data for Name: prodotto; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.prodotto VALUES (1, 'One Piece', 'Il leggendario manga di Eiichiro Oda che segue le avventure di Monkey D. Luffy e della sua ciurma di pirati alla ricerca del One Piece.');
INSERT INTO comicgalaxy.prodotto VALUES (2, 'Naruto', 'Serie di Masashi Kishimoto che racconta la crescita di Naruto Uzumaki, un giovane ninja con il sogno di diventare Hokage.');
INSERT INTO comicgalaxy.prodotto VALUES (3, 'Attack on Titan', 'Manga di Hajime Isayama ambientato in un mondo dove l’umanità lotta contro giganti che divorano esseri umani.');
INSERT INTO comicgalaxy.prodotto VALUES (4, 'Dragon Ball', 'Iconica opera di Akira Toriyama che narra le avventure di Goku nella ricerca delle sfere del drago.');
INSERT INTO comicgalaxy.prodotto VALUES (5, 'My Hero Academia', 'Serie di Kōhei Horikoshi su un mondo dove quasi tutti hanno superpoteri e un ragazzo senza poteri sogna di diventare un eroe.');
INSERT INTO comicgalaxy.prodotto VALUES (6, 'Demon Slayer', 'Manga di Koyoharu Gotouge che segue Tanjiro Kamado nella sua lotta contro i demoni per salvare la sorella Nezuko.');
INSERT INTO comicgalaxy.prodotto VALUES (7, 'Death Note', 'Psicothriller di Tsugumi Ohba e Takeshi Obata incentrato su un quaderno che uccide chiunque vi sia scritto il nome.');
INSERT INTO comicgalaxy.prodotto VALUES (8, 'Fullmetal Alchemist', 'Opera di Hiromu Arakawa che racconta la storia dei fratelli Elric e della loro ricerca della pietra filosofale.');
INSERT INTO comicgalaxy.prodotto VALUES (9, 'Jujutsu Kaisen', 'Serie di Gege Akutami incentrata su maledizioni e stregoni, con protagonista Yuji Itadori.');
INSERT INTO comicgalaxy.prodotto VALUES (10, 'Bleach', 'Manga di Tite Kubo che segue Ichigo Kurosaki, un ragazzo che diventa un sostituto Shinigami per difendere gli esseri umani dagli spiriti maligni.');
INSERT INTO comicgalaxy.prodotto VALUES (11, 'Le Bizzarre Avventure di JoJo - Phantom Blood', 'Prima parte della saga di Hirohiko Araki segue Jonathan Joestar e il suo scontro con Dio Brando.');
INSERT INTO comicgalaxy.prodotto VALUES (12, 'Le Bizzarre Avventure di JoJo - Battle Tendency', 'Seconda parte con protagonista Joseph Joestar contro i misteriosi Uomini del Pilastro.');
INSERT INTO comicgalaxy.prodotto VALUES (13, 'Le Bizzarre Avventure di JoJo - Stardust Crusaders', 'Terza parte con Jotaro Kujo e i suoi compagni in viaggio verso l Egitto per sconfiggere Dio.');
INSERT INTO comicgalaxy.prodotto VALUES (14, 'Le Bizzarre Avventure di JoJo - Diamond is Unbreakable', 'Quarta parte ambientata a Morioh segue Josuke Higashikata e i suoi amici contro uno stand user serial killer.');
INSERT INTO comicgalaxy.prodotto VALUES (15, 'Le Bizzarre Avventure di JoJo - Golden Wind', 'Quinta parte ambientata in Italia racconta l ascesa di Giorno Giovanna nella gang Passione.');
INSERT INTO comicgalaxy.prodotto VALUES (16, 'Le Bizzarre Avventure di JoJo - Stone Ocean', 'Sesta parte con Jolyne Cujoh figlia di Jotaro che combatte in un carcere della Florida.');
INSERT INTO comicgalaxy.prodotto VALUES (17, 'Le Bizzarre Avventure di JoJo - Steel Ball Run', 'Settima parte ambientata in un universo alternativo segue Johnny Joestar in una corsa attraverso l America.');
INSERT INTO comicgalaxy.prodotto VALUES (18, 'Le Bizzarre Avventure di JoJo - JoJolion', 'Ottava parte ambientata a Morioh dopo un terremoto con protagonista Josuke Higashikata.');
INSERT INTO comicgalaxy.prodotto VALUES (19, 'Le Bizzarre Avventure di JoJo - The JOJOLands', 'Nona parte della saga segue Jodio Joestar in un viaggio criminale alle Hawaii.');
INSERT INTO comicgalaxy.prodotto VALUES (20, 'Chainsaw Man', 'Manga di Tatsuki Fujimoto che segue Denji un giovane cacciatore di demoni con poteri da motosega.');
INSERT INTO comicgalaxy.prodotto VALUES (21, 'Tokyo Revengers', 'Manga di Ken Wakui che unisce viaggi nel tempo e guerre tra gang di Tokyo.');
INSERT INTO comicgalaxy.prodotto VALUES (22, 'Spy x Family', 'Commedia di Tatsuya Endo su una famiglia formata da una spia un assassina e una bambina telepatica.');
INSERT INTO comicgalaxy.prodotto VALUES (23, 'One Punch Man', 'Parodia dei supereroi di ONE e Yusuke Murata con protagonista Saitama l eroe imbattibile con un solo pugno.');
INSERT INTO comicgalaxy.prodotto VALUES (24, 'Black Clover', 'Serie di Yuki Tabata su Asta un ragazzo senza poteri magici che sogna di diventare il Re dei Maghi.');
INSERT INTO comicgalaxy.prodotto VALUES (25, 'Blue Lock', 'Manga sportivo di Muneyuki Kaneshiro e Yusuke Nomura sul calcio e la ricerca dell attaccante perfetto.');
INSERT INTO comicgalaxy.prodotto VALUES (26, 'Haikyuu', 'Serie di Haruichi Furudate sul mondo della pallavolo scolastica e la crescita personale.');
INSERT INTO comicgalaxy.prodotto VALUES (27, 'Hunter x Hunter', 'Manga di Yoshihiro Togashi che segue Gon Freecss nella sua avventura per diventare un cacciatore.');
INSERT INTO comicgalaxy.prodotto VALUES (28, 'Berserk', 'Capolavoro dark fantasy di Kentaro Miura sulle battaglie di Guts contro forze demoniache.');
INSERT INTO comicgalaxy.prodotto VALUES (29, 'Vinland Saga', 'Manga storico di Makoto Yukimura che racconta la vita del guerriero vichingo Thorfinn.');
INSERT INTO comicgalaxy.prodotto VALUES (30, 'Solo Leveling', 'Manhwa coreano su Jinwoo Sung un cacciatore debole che diventa il piu forte di tutti.');
INSERT INTO comicgalaxy.prodotto VALUES (31, 'Tower of God', 'Manhwa di SIU che segue Bam nella misteriosa torre piena di prove e segreti.');
INSERT INTO comicgalaxy.prodotto VALUES (32, 'Fairy Tail', 'Serie di Hiro Mashima su una gilda di maghi avventurosi e i loro legami di amicizia.');
INSERT INTO comicgalaxy.prodotto VALUES (33, 'Edens Zero', 'Altro manga di Hiro Mashima ambientato nello spazio con temi di liberta e amicizia.');
INSERT INTO comicgalaxy.prodotto VALUES (34, 'The Promised Neverland', 'Thriller su bambini che tentano di fuggire da un orfanotrofio con oscuri segreti.');
INSERT INTO comicgalaxy.prodotto VALUES (35, 'Dr Stone', 'Serie di Riichiro Inagaki e Boichi su un mondo post apocalittico in cui la scienza deve ricostruire la civilta.');
INSERT INTO comicgalaxy.prodotto VALUES (36, 'Fire Force', 'Manga di Atsushi Okubo su pompieri con poteri sovrannaturali che combattono incendi umani spontanei.');
INSERT INTO comicgalaxy.prodotto VALUES (37, 'Dorohedoro', 'Manga dark e surreale di Q Hayashida su un uomo con la testa di lucertola in cerca della sua identita.');
INSERT INTO comicgalaxy.prodotto VALUES (38, 'Parasyte', 'Serie di Hitoshi Iwaaki su alieni parassiti che invadono corpi umani.');
INSERT INTO comicgalaxy.prodotto VALUES (39, 'Monster', 'Thriller psicologico di Naoki Urasawa su un medico e il suo incontro con un pericoloso bambino.');
INSERT INTO comicgalaxy.prodotto VALUES (40, '20th Century Boys', 'Manga di Urasawa su un gruppo di amici che affronta una misteriosa setta.');
INSERT INTO comicgalaxy.prodotto VALUES (41, 'Pluto', 'Riadattamento futuristico di Urasawa e Tezuka basato su Astroboy.');
INSERT INTO comicgalaxy.prodotto VALUES (42, 'Akira', 'Capolavoro cyberpunk di Katsuhiro Otomo ambientato nella Neo Tokyo post apocalittica.');
INSERT INTO comicgalaxy.prodotto VALUES (43, 'Neon Genesis Evangelion', 'Adattamento manga dell omonima serie anime di Hideaki Anno.');
INSERT INTO comicgalaxy.prodotto VALUES (44, 'Claymore', 'Dark fantasy di Norihiro Yagi su guerriere meta umane e meta mostri.');
INSERT INTO comicgalaxy.prodotto VALUES (45, 'Trigun', 'Manga di Yasuhiro Nightow su Vash the Stampede pistolero pacifista in un mondo desertico.');
INSERT INTO comicgalaxy.prodotto VALUES (46, 'Cowboy Bebop', 'Manga tratto dall anime cult di Shinichiro Watanabe con i cacciatori di taglie spaziali.');
INSERT INTO comicgalaxy.prodotto VALUES (47, 'Rurouni Kenshin', 'Storia del samurai vagabondo Kenshin Himura nel Giappone Meiji.');
INSERT INTO comicgalaxy.prodotto VALUES (48, 'GTO Great Teacher Onizuka', 'Commedia su un ex teppista diventato insegnante con metodi fuori dal comune.');
INSERT INTO comicgalaxy.prodotto VALUES (49, 'Initial D', 'Manga automobilistico di Shuichi Shigeno incentrato sulle corse di drifting nelle montagne giapponesi.');
INSERT INTO comicgalaxy.prodotto VALUES (50, 'Beastars', 'Manga di Paru Itagaki su un mondo di animali antropomorfi e tensioni sociali tra predatori e prede.');
INSERT INTO comicgalaxy.prodotto VALUES (51, 'Made in Abyss', 'Serie di Akihito Tsukushi su una ragazza e un androide che esplorano un enorme abisso pieno di misteri.');
INSERT INTO comicgalaxy.prodotto VALUES (52, 'Oshi no Ko', 'Manga di Aka Akasaka e Mengo Yokoyari sul mondo oscuro dello spettacolo giapponese.');
INSERT INTO comicgalaxy.prodotto VALUES (53, 'Kaguya sama Love is War', 'Commedia romantica su due studenti geniali che si sfidano a far confessare l altro.');
INSERT INTO comicgalaxy.prodotto VALUES (54, 'Baki the Grappler', 'Manga di Keisuke Itagaki sulle arti marziali e i combattimenti estremi.');
INSERT INTO comicgalaxy.prodotto VALUES (55, 'Hellsing', 'Serie di Kouta Hirano su una organizzazione che combatte vampiri e mostri.');
INSERT INTO comicgalaxy.prodotto VALUES (56, 'Gantz', 'Manga sci fi di Hiroya Oku su persone che combattono alieni dopo la morte.');
INSERT INTO comicgalaxy.prodotto VALUES (57, 'Blame', 'Manga cyberpunk di Tsutomu Nihei ambientato in un labirinto tecnologico infinito.');
INSERT INTO comicgalaxy.prodotto VALUES (58, 'Bungo Stray Dogs', 'Serie di Kafka Asagiri e Sango Harukawa su detective con poteri ispirati a scrittori famosi.');
INSERT INTO comicgalaxy.prodotto VALUES (59, 'Slam Dunk', 'Manga sportivo di Takehiko Inoue su Hanamichi Sakuragi e la sua squadra di basket.');
INSERT INTO comicgalaxy.prodotto VALUES (60, 'Thus Spoke Kishibe Rohan', 'Raccolta di storie brevi sul mangaka Kishibe Rohan personaggio di JoJo.');
INSERT INTO comicgalaxy.prodotto VALUES (61, 'Golden Kamuy', 'Serie storica di Satoru Noda sulla caccia a un tesoro nascosto nell Hokkaido del primo Novecento.');
INSERT INTO comicgalaxy.prodotto VALUES (62, 'Attack on Titan No Regrets', 'Prequel di Attack on Titan che racconta il passato di Levi Ackerman.');
INSERT INTO comicgalaxy.prodotto VALUES (63, 'Uzumaki', 'Horror psicologico di Junji Ito basato su spirali e fenomeni inspiegabili.');
INSERT INTO comicgalaxy.prodotto VALUES (64, 'Tomie', 'Classico horror di Junji Ito su una ragazza immortale che porta follia ovunque.');
INSERT INTO comicgalaxy.prodotto VALUES (65, 'Vagabond', 'Capolavoro di Takehiko Inoue sulla vita di Miyamoto Musashi.');
INSERT INTO comicgalaxy.prodotto VALUES (66, 'Real', 'Manga di Inoue che esplora la vita di atleti disabili e la loro crescita personale.');
INSERT INTO comicgalaxy.prodotto VALUES (67, 'Blue Period', 'Manga di Tsubasa Yamaguchi sulla scoperta dell’arte e della vocazione personale.');
INSERT INTO comicgalaxy.prodotto VALUES (68, 'Frieren', 'Manga fantasy che segue una maga immortale dopo la sconfitta del Re Demone.');
INSERT INTO comicgalaxy.prodotto VALUES (69, 'Kaiju No. 8', 'Serie d’azione su un uomo che ottiene il potere di trasformarsi in un kaiju.');
INSERT INTO comicgalaxy.prodotto VALUES (70, 'Mashle', 'Parodia fantasy dove un ragazzo senza magia usa solo la forza fisica.');
INSERT INTO comicgalaxy.prodotto VALUES (71, 'Sakamoto Days', 'Commedia d’azione su un ex assassino che gestisce un minimarket.');
INSERT INTO comicgalaxy.prodotto VALUES (72, 'Blue Giant', 'Storia di crescita attraverso la musica jazz.');
INSERT INTO comicgalaxy.prodotto VALUES (73, 'Kingdom', 'Epico manga storico sulle guerre e l’unificazione della Cina.');
INSERT INTO comicgalaxy.prodotto VALUES (74, 'Record of Ragnarok', 'Serie in cui gli dei combattono campioni umani in duelli al limite.');
INSERT INTO comicgalaxy.prodotto VALUES (75, 'Hell’s Paradise', 'Manga d’azione ambientato su un’isola ricca di misteri mortali.');
INSERT INTO comicgalaxy.prodotto VALUES (76, 'Radiant', 'Manga francese in stile shonen con maghi perseguitati.');
INSERT INTO comicgalaxy.prodotto VALUES (77, 'The Boxer', 'Manhwa psicologico sulle dinamiche del pugilato professionistico.');
INSERT INTO comicgalaxy.prodotto VALUES (78, 'Red Sprite', 'Breve manga steampunk su rivoluzione e libertà.');
INSERT INTO comicgalaxy.prodotto VALUES (79, 'Sun-Ken Rock', 'Manhwa d’azione su un ragazzo che guida una gang per proteggere la donna amata.');
INSERT INTO comicgalaxy.prodotto VALUES (80, 'Bastard!!', 'Storico manga dark fantasy con magia, demoni e power metal.');
INSERT INTO comicgalaxy.prodotto VALUES (81, 'Magic Knight Rayearth', 'Classico di CLAMP che unisce fantasy e mecha.');
INSERT INTO comicgalaxy.prodotto VALUES (83, 'D.Gray-man', 'Serie dark su esorcisti che combattono demoni meccanici.');
INSERT INTO comicgalaxy.prodotto VALUES (84, 'Noragami', 'Avventura urbana che segue una divinità caduta in disgrazia.');
INSERT INTO comicgalaxy.prodotto VALUES (85, 'Komi Can’t Communicate', 'Commedia scolastica su una ragazza con ansia sociale estrema.');
INSERT INTO comicgalaxy.prodotto VALUES (86, 'Ancient Magus Bride', 'Fantasy romantico tra magia, folklore e crescita personale.');
INSERT INTO comicgalaxy.prodotto VALUES (87, 'La leggenda di Arslan', 'Manga storico basato sui romanzi fantasy di Tanaka Yoshiki.');
INSERT INTO comicgalaxy.prodotto VALUES (88, 'Yona of the Dawn', 'Shojo fantasy sulla crescita di una principessa esiliata.');
INSERT INTO comicgalaxy.prodotto VALUES (89, 'Zetman', 'Manga dark e fantascientifico con superpoteri e conflitti morali.');
INSERT INTO comicgalaxy.prodotto VALUES (90, 'Air Gear', 'Serie sportiva futuristica basata sui pattini motorizzati.');
INSERT INTO comicgalaxy.prodotto VALUES (91, 'Black Lagoon', 'Azione cruda su un gruppo di mercenari in Asia orientale.');
INSERT INTO comicgalaxy.prodotto VALUES (92, 'Deadman Wonderland', 'Thriller ambientato in una prigione spettacolo dove i detenuti combattono per sopravvivere.');
INSERT INTO comicgalaxy.prodotto VALUES (93, 'Pluto', 'Rivisitazione adulta di Naoki Urasawa del classico Astro Boy, dal tono thriller e drammatico.');
INSERT INTO comicgalaxy.prodotto VALUES (94, 'Beastars', 'Serie ambientata in un mondo di animali antropomorfi che affrontano tensioni sociali e identitarie.');
INSERT INTO comicgalaxy.prodotto VALUES (95, 'Chainsaw Man – Buddy Stories', 'Raccolta di racconti spin-off che approfondisce i rapporti tra i personaggi principali.');
INSERT INTO comicgalaxy.prodotto VALUES (96, 'Spy x Family: Family Portrait', 'Volume illustrato ricco di storie brevi e contenuti extra sulla famiglia Forger.');
INSERT INTO comicgalaxy.prodotto VALUES (98, 'Eden: It’s an Endless World!', 'Serie sci-fi matura che esplora geopolitica, cyberpunk e sopravvivenza umana.');
INSERT INTO comicgalaxy.prodotto VALUES (99, 'BLAME!', 'Manga dark sci-fi di Tsutomu Nihei ambientato in una megastruttura infinita.');
INSERT INTO comicgalaxy.prodotto VALUES (100, 'Ajin', 'Thriller sovrannaturale dove esseri immortali vengono perseguitati dai governi.');
INSERT INTO comicgalaxy.prodotto VALUES (97, 'Vivy Prototype', 'Romanzo adattato a manga in cui un''intelligenza artificiale cerca di salvare l''umanità.');


--
-- TOC entry 3700 (class 0 OID 16494)
-- Dependencies: 229
-- Data for Name: tessera; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.tessera VALUES (1, 4297, 'RSSALC99A41F205X', 1, '2030-11-05', '2025-10-30', false);
INSERT INTO comicgalaxy.tessera VALUES (6, 4082, 'FLCFRC98C12F205D', 1, '2026-12-12', '2025-12-10', false);


--
-- TOC entry 3687 (class 0 OID 16390)
-- Dependencies: 216
-- Data for Name: utente; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.utente VALUES ('federico.falcone@cliente.it', '+39061234567', '$2y$10$ygeXVvwWdGgH.BSthoHhqukkA0R7wYWfm9d71pLMilu4QOL8hGpXy', false);
INSERT INTO comicgalaxy.utente VALUES ('federico.falcone@comicgalaxy.it', '0123456789', '$2y$10$nk60OIAEumSufEkspf2vAOslEYEabgewYlNRjIYCHqNjICzfYaS6y', false);
INSERT INTO comicgalaxy.utente VALUES ('elena.monti@example.it', '3391112223', '$2y$10$SMmbhhrh6YOs6tH8cMV5GOm3iiQgEuKWNNvoPAVPyvzlyVRA7fOCe', false);
INSERT INTO comicgalaxy.utente VALUES ('marco.rossi@example.it', '3331234567', '$2y$10$JdSZzbF3eYD2qA2NjTQgwuNFzoBic17HsgOEA9WAdQ1z4Y6DmmVQq', false);
INSERT INTO comicgalaxy.utente VALUES ('bruno.bianchi@example.it', '3479876543', '$2y$10$13sVWW7rH8AhSznfjrGN2./AwFl24f7O9gKl16Ues1I0YsVP7n.6O', true);
INSERT INTO comicgalaxy.utente VALUES ('alice.rossi@example.it', '3351234567', '$2y$10$z9kxZdz2dj2iO6nNCgYyy.Lzv7lN35wuawET0fTKMPAwEZfvlyDKu', false);
INSERT INTO comicgalaxy.utente VALUES ('davide.ferri@example.it', '3334445556', '$2y$10$Fau6q.n9WvwDL3MumcU00eXUdpBea.djOOhXhxCxCJTdsF2ErwXmK', false);
INSERT INTO comicgalaxy.utente VALUES ('carlas.verdi@example.it', '3382223398', '$2y$10$b.nDiOpvBWkhyebxacGnBudU2VJ.uUyuE40rVD76oUzyXp9Wgaopu', false);
INSERT INTO comicgalaxy.utente VALUES ('francesco.bernoulli@gmail.com', '0123456789', '$2y$10$dOxNHezsGwONXqxjy/WoCuLzuqUZ4DBiqQHZ0QdbZ6rG/.J5gfs/C', false);
INSERT INTO comicgalaxy.utente VALUES ('elena.colombo@comicgalaxy.it', '1256985314', '$2y$10$dWn.XTPiGpw36Qo7QaPgVuG/CeGfDuT0CAwUK/l0dgLC.L8RtzFY.', false);
INSERT INTO comicgalaxy.utente VALUES ('gennaro.esposito@comicgalaxy.it', '0123456789', '$2y$10$JYI0HV4dQ6i0JWzZLe.pL.BzK3CzCfqOiVldQmmqwTm8hKbiShmXe', false);
INSERT INTO comicgalaxy.utente VALUES ('luca.rossi@comicgalaxy.it', '0123456789', '$2y$10$.2rpLRidteZuJ5GDW8Nq4eHEuABjYLw1gz7XTWsi2SnuC4z8MpXgC', false);
INSERT INTO comicgalaxy.utente VALUES ('giulia.luna@comicgalaxy.it', '3285556660', '$2y$10$6wJViwp68Q8MJDyyInQT.ulcRarfHzaOeY/t0vThKDvpCqKdjTZDG', false);
INSERT INTO comicgalaxy.utente VALUES ('hugo.marini@comicgalaxy.it', '3294443332', '$2y$10$tjArZJadm9iGifznzQVIKuCa4YCx8CuoxEAH2CTuUOkO8wUfP30Oq', false);
INSERT INTO comicgalaxy.utente VALUES ('irene.sarti@comicgalaxy.it', '3348887771', '$2y$10$pMCP72w7zs4zhlp5hBmD/O5rk7h9K5TdO2dwX71MOTVlvRiUf7VVO', false);
INSERT INTO comicgalaxy.utente VALUES ('luca.moretti@comicgalaxy.it', '3312223334', '$2y$10$C0bq28r7mVRv03hhONCKc.TWUoDcZGhhXYZnNwYTDHDE0CdaeitZy', false);
INSERT INTO comicgalaxy.utente VALUES ('fabio.gallo@comicgalaxy.it', '3467778889', '$2y$10$J2VVYpHJgm1Xoq210Up.WuPMG/kwpzQBGyqizJQnSIvSyhU.Prnxy', false);


--
-- TOC entry 3720 (class 0 OID 0)
-- Dependencies: 226
-- Name: fattura_id_seq; Type: SEQUENCE SET; Schema: comicgalaxy; Owner: federico
--

SELECT pg_catalog.setval('comicgalaxy.fattura_id_seq', 105, true);


--
-- TOC entry 3721 (class 0 OID 0)
-- Dependencies: 222
-- Name: indirizzo_id_seq; Type: SEQUENCE SET; Schema: comicgalaxy; Owner: federico
--

SELECT pg_catalog.setval('comicgalaxy.indirizzo_id_seq', 39, true);


--
-- TOC entry 3722 (class 0 OID 0)
-- Dependencies: 219
-- Name: negozio_codice_seq; Type: SEQUENCE SET; Schema: comicgalaxy; Owner: federico
--

SELECT pg_catalog.setval('comicgalaxy.negozio_codice_seq', 24, true);


--
-- TOC entry 3723 (class 0 OID 0)
-- Dependencies: 231
-- Name: ordine_id_seq; Type: SEQUENCE SET; Schema: comicgalaxy; Owner: federico
--

SELECT pg_catalog.setval('comicgalaxy.ordine_id_seq', 19, true);


--
-- TOC entry 3724 (class 0 OID 0)
-- Dependencies: 224
-- Name: prodotto_id_seq; Type: SEQUENCE SET; Schema: comicgalaxy; Owner: federico
--

SELECT pg_catalog.setval('comicgalaxy.prodotto_id_seq', 62, true);


--
-- TOC entry 3725 (class 0 OID 0)
-- Dependencies: 228
-- Name: tessera_id_seq; Type: SEQUENCE SET; Schema: comicgalaxy; Owner: federico
--

SELECT pg_catalog.setval('comicgalaxy.tessera_id_seq', 6, true);


--
-- TOC entry 3463 (class 2606 OID 16401)
-- Name: cliente cliente_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.cliente
    ADD CONSTRAINT cliente_pkey PRIMARY KEY (cf);


--
-- TOC entry 3487 (class 2606 OID 16561)
-- Name: dettaglio_ordini dettaglio_ordini_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.dettaglio_ordini
    ADD CONSTRAINT dettaglio_ordini_pkey PRIMARY KEY (id_prodotto, id_ordine);


--
-- TOC entry 3493 (class 2606 OID 16596)
-- Name: dettaglio_fattura fattura_negozio_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.dettaglio_fattura
    ADD CONSTRAINT fattura_negozio_pkey PRIMARY KEY (id_fattura, id_prodotto);


--
-- TOC entry 3479 (class 2606 OID 16482)
-- Name: fattura fattura_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fattura
    ADD CONSTRAINT fattura_pkey PRIMARY KEY (id);


--
-- TOC entry 3483 (class 2606 OID 16668)
-- Name: fornitore fornitore_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitore
    ADD CONSTRAINT fornitore_pkey PRIMARY KEY (p_iva);


--
-- TOC entry 3495 (class 2606 OID 16694)
-- Name: fornitura_fornitore fornitura_fornitore_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitura_fornitore
    ADD CONSTRAINT fornitura_fornitore_pkey PRIMARY KEY (p_iva_fornitore, id_prodotto);


--
-- TOC entry 3489 (class 2606 OID 16579)
-- Name: fornitura_negozio fornitura_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitura_negozio
    ADD CONSTRAINT fornitura_pkey PRIMARY KEY (id_negozio, id_prodotto);


--
-- TOC entry 3473 (class 2606 OID 16464)
-- Name: indirizzo indirizzo_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.indirizzo
    ADD CONSTRAINT indirizzo_pkey PRIMARY KEY (id);


--
-- TOC entry 3475 (class 2606 OID 17128)
-- Name: indirizzo indirizzo_univoco; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.indirizzo
    ADD CONSTRAINT indirizzo_univoco UNIQUE (citta, via, civico);


--
-- TOC entry 3467 (class 2606 OID 16411)
-- Name: manager manager_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.manager
    ADD CONSTRAINT manager_pkey PRIMARY KEY (mail);


--
-- TOC entry 3469 (class 2606 OID 16428)
-- Name: negozio negozio_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.negozio
    ADD CONSTRAINT negozio_pkey PRIMARY KEY (id);


--
-- TOC entry 3471 (class 2606 OID 16807)
-- Name: orario orario_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.orario
    ADD CONSTRAINT orario_pkey PRIMARY KEY (giorno, id_negozio);


--
-- TOC entry 3485 (class 2606 OID 16528)
-- Name: ordine ordine_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.ordine
    ADD CONSTRAINT ordine_pkey PRIMARY KEY (id);


--
-- TOC entry 3477 (class 2606 OID 16473)
-- Name: prodotto prodotto_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.prodotto
    ADD CONSTRAINT prodotto_pkey PRIMARY KEY (id);


--
-- TOC entry 3481 (class 2606 OID 16501)
-- Name: tessera tessera_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.tessera
    ADD CONSTRAINT tessera_pkey PRIMARY KEY (id);


--
-- TOC entry 3491 (class 2606 OID 17065)
-- Name: fornitura_negozio unq_fornitura; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitura_negozio
    ADD CONSTRAINT unq_fornitura UNIQUE (id_negozio, id_prodotto);


--
-- TOC entry 3465 (class 2606 OID 17112)
-- Name: cliente utente_mail_unique; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.cliente
    ADD CONSTRAINT utente_mail_unique UNIQUE (mail);


--
-- TOC entry 3461 (class 2606 OID 16396)
-- Name: utente utente_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.utente
    ADD CONSTRAINT utente_pkey PRIMARY KEY (mail);


--
-- TOC entry 3680 (class 2618 OID 17055)
-- Name: v_storico_ordini_fornitori _RETURN; Type: RULE; Schema: comicgalaxy; Owner: federico
--

CREATE OR REPLACE VIEW comicgalaxy.v_storico_ordini_fornitori AS
 SELECT f.p_iva,
    f.nome,
    o.id AS id_ordine,
    n.id AS id_negozio,
    n.nome AS nome_negozio,
    o.data AS data_ordine,
    o.data_consegna,
    o.ritirato,
    sum(((d.quantita)::numeric * d.prezzo)) AS totale
   FROM (((comicgalaxy.fornitore f
     JOIN comicgalaxy.ordine o ON ((o.fornitore = f.p_iva)))
     JOIN comicgalaxy.negozio n ON ((o.negozio = n.id)))
     JOIN comicgalaxy.dettaglio_ordini d ON ((d.id_ordine = o.id)))
  GROUP BY f.p_iva, o.id, n.id, n.nome, o.data, o.data_consegna, o.ritirato;


--
-- TOC entry 3519 (class 2620 OID 17182)
-- Name: utente trg_check_email; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trg_check_email BEFORE INSERT OR UPDATE ON comicgalaxy.utente FOR EACH ROW EXECUTE FUNCTION comicgalaxy.check_email_format();


--
-- TOC entry 3530 (class 2620 OID 17063)
-- Name: ordine trg_check_modifica_data; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trg_check_modifica_data BEFORE UPDATE ON comicgalaxy.ordine FOR EACH ROW EXECUTE FUNCTION comicgalaxy.check_modifica_data();


--
-- TOC entry 3525 (class 2620 OID 16785)
-- Name: orario trg_check_orario; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trg_check_orario BEFORE INSERT OR UPDATE ON comicgalaxy.orario FOR EACH ROW EXECUTE FUNCTION comicgalaxy.check_orario_apertura_chiusura();


--
-- TOC entry 3527 (class 2620 OID 17190)
-- Name: fornitore trg_check_p_iva; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trg_check_p_iva BEFORE INSERT OR UPDATE ON comicgalaxy.fornitore FOR EACH ROW EXECUTE FUNCTION comicgalaxy.check_p_iva();


--
-- TOC entry 3528 (class 2620 OID 17186)
-- Name: fornitore trg_check_telefono; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trg_check_telefono BEFORE INSERT OR UPDATE ON comicgalaxy.fornitore FOR EACH ROW EXECUTE FUNCTION comicgalaxy.check_telefono_format();


--
-- TOC entry 3520 (class 2620 OID 17185)
-- Name: utente trg_check_telefono; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trg_check_telefono BEFORE INSERT OR UPDATE ON comicgalaxy.utente FOR EACH ROW EXECUTE FUNCTION comicgalaxy.check_telefono_format();


--
-- TOC entry 3532 (class 2620 OID 16666)
-- Name: dettaglio_ordini trigger_aggiorna_disponibilita_fornitore; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trigger_aggiorna_disponibilita_fornitore AFTER INSERT ON comicgalaxy.dettaglio_ordini FOR EACH ROW EXECUTE FUNCTION comicgalaxy.aggiorna_disponibilita_fornitore();


--
-- TOC entry 3526 (class 2620 OID 16709)
-- Name: fattura trigger_aggiorna_saldo_punti; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trigger_aggiorna_saldo_punti AFTER INSERT ON comicgalaxy.fattura FOR EACH ROW EXECUTE FUNCTION comicgalaxy.aggiorna_saldo_punti();


--
-- TOC entry 3522 (class 2620 OID 17180)
-- Name: cliente trigger_check_cf; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trigger_check_cf BEFORE INSERT OR UPDATE ON comicgalaxy.cliente FOR EACH ROW EXECUTE FUNCTION comicgalaxy.check_codice_fiscale();


--
-- TOC entry 3529 (class 2620 OID 17187)
-- Name: fornitore trigger_check_email; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trigger_check_email BEFORE INSERT OR UPDATE ON comicgalaxy.fornitore FOR EACH ROW EXECUTE FUNCTION comicgalaxy.check_email_format();


--
-- TOC entry 3523 (class 2620 OID 16783)
-- Name: negozio trigger_check_manager_disponibile; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trigger_check_manager_disponibile BEFORE INSERT OR UPDATE ON comicgalaxy.negozio FOR EACH ROW EXECUTE FUNCTION comicgalaxy.check_manager_disponibile();


--
-- TOC entry 3524 (class 2620 OID 17100)
-- Name: negozio trigger_chiusura_negozio; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trigger_chiusura_negozio BEFORE UPDATE OF data_chiusura ON comicgalaxy.negozio FOR EACH ROW EXECUTE FUNCTION comicgalaxy.check_riapertura_negozio();


--
-- TOC entry 3531 (class 2620 OID 16730)
-- Name: ordine trigger_ritiro; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trigger_ritiro AFTER UPDATE OF ritirato ON comicgalaxy.ordine FOR EACH ROW EXECUTE FUNCTION comicgalaxy.ritiro_ordine();


--
-- TOC entry 3521 (class 2620 OID 17198)
-- Name: utente trigger_sospendi_tessera; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trigger_sospendi_tessera AFTER UPDATE OF sospeso ON comicgalaxy.utente FOR EACH ROW EXECUTE FUNCTION comicgalaxy.sospendi_tessera();


--
-- TOC entry 3496 (class 2606 OID 16402)
-- Name: cliente cliente_mail_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.cliente
    ADD CONSTRAINT cliente_mail_fkey FOREIGN KEY (mail) REFERENCES comicgalaxy.utente(mail) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3515 (class 2606 OID 17202)
-- Name: dettaglio_fattura dettaglio_fattura_prodotto_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.dettaglio_fattura
    ADD CONSTRAINT dettaglio_fattura_prodotto_fkey FOREIGN KEY (id_prodotto) REFERENCES comicgalaxy.prodotto(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3511 (class 2606 OID 16567)
-- Name: dettaglio_ordini dettaglio_ordini_id_ordine_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.dettaglio_ordini
    ADD CONSTRAINT dettaglio_ordini_id_ordine_fkey FOREIGN KEY (id_ordine) REFERENCES comicgalaxy.ordine(id);


--
-- TOC entry 3512 (class 2606 OID 16562)
-- Name: dettaglio_ordini dettaglio_ordini_id_prodotto_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.dettaglio_ordini
    ADD CONSTRAINT dettaglio_ordini_id_prodotto_fkey FOREIGN KEY (id_prodotto) REFERENCES comicgalaxy.prodotto(id);


--
-- TOC entry 3504 (class 2606 OID 16483)
-- Name: fattura fattura_cf_cliente_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fattura
    ADD CONSTRAINT fattura_cf_cliente_fkey FOREIGN KEY (cf_cliente) REFERENCES comicgalaxy.cliente(cf) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3505 (class 2606 OID 16488)
-- Name: fattura fattura_codice_negozio_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fattura
    ADD CONSTRAINT fattura_codice_negozio_fkey FOREIGN KEY (codice_negozio) REFERENCES comicgalaxy.negozio(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3516 (class 2606 OID 16597)
-- Name: dettaglio_fattura fattura_negozio_id_fattura_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.dettaglio_fattura
    ADD CONSTRAINT fattura_negozio_id_fattura_fkey FOREIGN KEY (id_fattura) REFERENCES comicgalaxy.fattura(id);


--
-- TOC entry 3497 (class 2606 OID 17113)
-- Name: cliente fk_cliente_utente_mail; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.cliente
    ADD CONSTRAINT fk_cliente_utente_mail FOREIGN KEY (mail) REFERENCES comicgalaxy.utente(mail) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3501 (class 2606 OID 16551)
-- Name: negozio fk_indirizzo; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.negozio
    ADD CONSTRAINT fk_indirizzo FOREIGN KEY (id_indirizzo) REFERENCES comicgalaxy.indirizzo(id);


--
-- TOC entry 3502 (class 2606 OID 16449)
-- Name: negozio fk_manager; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.negozio
    ADD CONSTRAINT fk_manager FOREIGN KEY (manager) REFERENCES comicgalaxy.manager(mail) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3508 (class 2606 OID 16517)
-- Name: fornitore fornitore_indirizzo_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitore
    ADD CONSTRAINT fornitore_indirizzo_fkey FOREIGN KEY (indirizzo) REFERENCES comicgalaxy.indirizzo(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3517 (class 2606 OID 16626)
-- Name: fornitura_fornitore fornitura_fornitore_id_prodotto_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitura_fornitore
    ADD CONSTRAINT fornitura_fornitore_id_prodotto_fkey FOREIGN KEY (id_prodotto) REFERENCES comicgalaxy.prodotto(id);


--
-- TOC entry 3518 (class 2606 OID 17146)
-- Name: fornitura_fornitore fornitura_fornitore_p_iva_fornitore_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitura_fornitore
    ADD CONSTRAINT fornitura_fornitore_p_iva_fornitore_fkey FOREIGN KEY (p_iva_fornitore) REFERENCES comicgalaxy.fornitore(p_iva) ON UPDATE CASCADE;


--
-- TOC entry 3513 (class 2606 OID 16580)
-- Name: fornitura_negozio fornitura_id_negozio_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitura_negozio
    ADD CONSTRAINT fornitura_id_negozio_fkey FOREIGN KEY (id_negozio) REFERENCES comicgalaxy.negozio(id);


--
-- TOC entry 3514 (class 2606 OID 16585)
-- Name: fornitura_negozio fornitura_id_prodotto_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitura_negozio
    ADD CONSTRAINT fornitura_id_prodotto_fkey FOREIGN KEY (id_prodotto) REFERENCES comicgalaxy.prodotto(id);


--
-- TOC entry 3498 (class 2606 OID 17101)
-- Name: cliente mail; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.cliente
    ADD CONSTRAINT mail FOREIGN KEY (mail) REFERENCES comicgalaxy.utente(mail) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3499 (class 2606 OID 17106)
-- Name: manager mail; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.manager
    ADD CONSTRAINT mail FOREIGN KEY (mail) REFERENCES comicgalaxy.utente(mail) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3500 (class 2606 OID 16412)
-- Name: manager manager_mail_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.manager
    ADD CONSTRAINT manager_mail_fkey FOREIGN KEY (mail) REFERENCES comicgalaxy.utente(mail) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3503 (class 2606 OID 16439)
-- Name: orario orario_codice_negozio_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.orario
    ADD CONSTRAINT orario_codice_negozio_fkey FOREIGN KEY (id_negozio) REFERENCES comicgalaxy.negozio(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3509 (class 2606 OID 16684)
-- Name: ordine ordine_fornitore_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.ordine
    ADD CONSTRAINT ordine_fornitore_fkey FOREIGN KEY (fornitore) REFERENCES comicgalaxy.fornitore(p_iva) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3510 (class 2606 OID 16529)
-- Name: ordine ordine_negozio_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.ordine
    ADD CONSTRAINT ordine_negozio_fkey FOREIGN KEY (negozio) REFERENCES comicgalaxy.negozio(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3506 (class 2606 OID 16502)
-- Name: tessera tessera_cf_cliente_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.tessera
    ADD CONSTRAINT tessera_cf_cliente_fkey FOREIGN KEY (cf_cliente) REFERENCES comicgalaxy.cliente(cf) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3507 (class 2606 OID 16507)
-- Name: tessera tessera_codice_negozio_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.tessera
    ADD CONSTRAINT tessera_codice_negozio_fkey FOREIGN KEY (id_negozio) REFERENCES comicgalaxy.negozio(id) ON UPDATE CASCADE ON DELETE CASCADE;


-- Completed on 2025-12-13 11:25:58 CET

--
-- PostgreSQL database dump complete
--

\unrestrict LHLcsynPeEFs5p7TAlNxaod2TJkhmL0cLnLPJ4XHYpI75aRwDTpO763FHxxB7Xe

