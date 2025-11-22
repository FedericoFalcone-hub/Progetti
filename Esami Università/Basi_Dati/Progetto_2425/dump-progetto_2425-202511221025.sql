--
-- PostgreSQL database dump
--

\restrict hZifBaa36lgqT9aTGbtWOCU29fa63uCh4GtE4n6zrqQzlapUv6ujyNIto49k8TO

-- Dumped from database version 16.10 (Ubuntu 16.10-0ubuntu0.24.04.1)
-- Dumped by pg_dump version 16.10 (Ubuntu 16.10-0ubuntu0.24.04.1)

-- Started on 2025-11-22 10:25:31 CET

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
-- TOC entry 3644 (class 1262 OID 16389)
-- Name: progetto_2425; Type: DATABASE; Schema: -; Owner: postgres
--

CREATE DATABASE progetto_2425 WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'en_US.UTF-8';


ALTER DATABASE progetto_2425 OWNER TO postgres;

\unrestrict hZifBaa36lgqT9aTGbtWOCU29fa63uCh4GtE4n6zrqQzlapUv6ujyNIto49k8TO
\connect progetto_2425
\restrict hZifBaa36lgqT9aTGbtWOCU29fa63uCh4GtE4n6zrqQzlapUv6ujyNIto49k8TO

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
-- TOC entry 935 (class 1247 OID 16787)
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
-- TOC entry 943 (class 1247 OID 16839)
-- Name: stato_ordine; Type: TYPE; Schema: comicgalaxy; Owner: federico
--

CREATE TYPE comicgalaxy.stato_ordine AS ENUM (
    'in arrivo',
    'da ritirare',
    'ritirato'
);


ALTER TYPE comicgalaxy.stato_ordine OWNER TO federico;

--
-- TOC entry 946 (class 1247 OID 16847)
-- Name: tipo_prodotto_quantita; Type: TYPE; Schema: comicgalaxy; Owner: federico
--

CREATE TYPE comicgalaxy.tipo_prodotto_quantita AS (
	id_prodotto integer,
	quantita integer
);


ALTER TYPE comicgalaxy.tipo_prodotto_quantita OWNER TO federico;

--
-- TOC entry 258 (class 1255 OID 16665)
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
-- TOC entry 267 (class 1255 OID 16654)
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
		
	select data_scadenza into scadenza
	from comicgalaxy.tessera
	where cf_cliente=new.cf_cliente and id_negozio=new.codice_negozio;

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
		return new;
end
$$;


ALTER FUNCTION comicgalaxy.aggiorna_saldo_punti() OWNER TO federico;

--
-- TOC entry 264 (class 1255 OID 16722)
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
-- TOC entry 263 (class 1255 OID 16782)
-- Name: check_manager_disponibile(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.check_manager_disponibile() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Controlla se il manager è già associato a un altro negozio
    IF EXISTS (
        SELECT 1
        FROM negozio
        WHERE id_manager = NEW.id_manager
    ) THEN
        RAISE EXCEPTION 'Il manager % è già associato ad un altro negozio', NEW.id_manager;
    END IF;

    RETURN NEW;
END;
$$;


ALTER FUNCTION comicgalaxy.check_manager_disponibile() OWNER TO federico;

--
-- TOC entry 265 (class 1255 OID 16784)
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
-- TOC entry 261 (class 1255 OID 16743)
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
-- TOC entry 260 (class 1255 OID 16978)
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
BEGIN
    -- Controlla che gli array abbiano la stessa lunghezza
    IF coalesce(array_length(p_prodotti,1),0) <> coalesce(array_length(p_quantita,1),0) THEN
        RAISE EXCEPTION 'Gli array id_prodotto e quantita devono avere la stessa lunghezza';
    END IF;

    n_prodotti := coalesce(array_length(p_prodotti,1),0);
	i:=1;
	f_quantita=0;
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
        FROM comicgalaxy.fornitura_fornitore
        WHERE id_prodotto = p_prodotti[i] and quantita > 0
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

        -- Inserisci nel dettaglio_ordini
        INSERT INTO comicgalaxy.dettaglio_ordini(id_prodotto, id_ordine, quantita, prezzo)
        VALUES (p_prodotti[i], f_id_ordine, q, f_prezzo);
		
		if f_quantita >= p_quantita[i] then
			i:=i+1;
		end if;
		RAISE NOTICE '%',
        f_fornitore;
    END LOOP;

END;
$$;


ALTER FUNCTION comicgalaxy.ordina_prodotti(p_id_negozio integer, p_prodotti integer[], p_quantita integer[]) OWNER TO federico;

--
-- TOC entry 259 (class 1255 OID 16708)
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
-- TOC entry 262 (class 1255 OID 16989)
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
-- TOC entry 266 (class 1255 OID 16728)
-- Name: ritiro_ordine(); Type: FUNCTION; Schema: comicgalaxy; Owner: federico
--

CREATE FUNCTION comicgalaxy.ritiro_ordine() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN

        UPDATE comicgalaxy.fornitura_negozio
        SET quantita = fornitura_negozio.quantita + o.quantita
        FROM comicgalaxy.dettaglio_ordini as o
        WHERE o.id_prodotto = fornitura_negozio.id_prodotto
          AND o.id_ordine = NEW.id;

    RETURN NEW;
END;
$$;


ALTER FUNCTION comicgalaxy.ritiro_ordine() OWNER TO federico;

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
    prezzo numeric(10,2) NOT NULL,
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
-- TOC entry 3645 (class 0 OID 0)
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
    nome character varying NOT NULL
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
    prezzo numeric(10,2) NOT NULL,
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
    via character varying(255) NOT NULL
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
-- TOC entry 3646 (class 0 OID 0)
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
-- TOC entry 3647 (class 0 OID 0)
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
    data date DEFAULT CURRENT_DATE NOT NULL
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
-- TOC entry 3648 (class 0 OID 0)
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
-- TOC entry 3649 (class 0 OID 0)
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
-- TOC entry 3650 (class 0 OID 0)
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
    password character varying(255) NOT NULL
);


ALTER TABLE comicgalaxy.utente OWNER TO federico;

--
-- TOC entry 239 (class 1259 OID 16778)
-- Name: v_clienti_punti_elevati; Type: VIEW; Schema: comicgalaxy; Owner: federico
--

CREATE VIEW comicgalaxy.v_clienti_punti_elevati AS
 SELECT c.cf AS cf_cliente,
    c.nome AS nome_cliente,
    c.cognome AS cognome_cliente,
    t.id_negozio,
    t.id AS id_tessera,
    t.data_emissione,
    t.saldo AS saldo_punti
   FROM (comicgalaxy.cliente c
     JOIN comicgalaxy.tessera t ON ((c.cf = t.cf_cliente)))
  WHERE (t.saldo > 300);


ALTER VIEW comicgalaxy.v_clienti_punti_elevati OWNER TO federico;

--
-- TOC entry 243 (class 1259 OID 16984)
-- Name: v_prodotti_ordinabili; Type: VIEW; Schema: comicgalaxy; Owner: federico
--

CREATE VIEW comicgalaxy.v_prodotti_ordinabili AS
 SELECT p.id,
    p.nome AS nome_prodotto,
    f.nome AS nome_fornitore,
    fo.prezzo,
    fo.quantita
   FROM ((comicgalaxy.prodotto p
     JOIN comicgalaxy.fornitura_fornitore fo ON ((fo.id_prodotto = p.id)))
     JOIN comicgalaxy.fornitore f ON ((f.p_iva = fo.p_iva_fornitore)))
  WHERE (fo.quantita > 0);


ALTER VIEW comicgalaxy.v_prodotti_ordinabili OWNER TO federico;

--
-- TOC entry 241 (class 1259 OID 16833)
-- Name: v_storico_ordini_fornitori; Type: VIEW; Schema: comicgalaxy; Owner: federico
--

CREATE VIEW comicgalaxy.v_storico_ordini_fornitori AS
 SELECT f.p_iva,
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


ALTER VIEW comicgalaxy.v_storico_ordini_fornitori OWNER TO federico;

--
-- TOC entry 238 (class 1259 OID 16761)
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
-- TOC entry 237 (class 1259 OID 16757)
-- Name: v_tessere; Type: VIEW; Schema: comicgalaxy; Owner: federico
--

CREATE VIEW comicgalaxy.v_tessere AS
 SELECT t.id_negozio,
    n.nome AS nome_negozio,
    t.cf_cliente,
    t.saldo,
    t.data_emissione,
    t.data_scadenza
   FROM (comicgalaxy.tessera t
     JOIN comicgalaxy.negozio n ON ((n.id = t.id_negozio)));


ALTER VIEW comicgalaxy.v_tessere OWNER TO federico;

--
-- TOC entry 3396 (class 2604 OID 16478)
-- Name: fattura id; Type: DEFAULT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fattura ALTER COLUMN id SET DEFAULT nextval('comicgalaxy.fattura_id_seq'::regclass);


--
-- TOC entry 3394 (class 2604 OID 16462)
-- Name: indirizzo id; Type: DEFAULT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.indirizzo ALTER COLUMN id SET DEFAULT nextval('comicgalaxy.indirizzo_id_seq'::regclass);


--
-- TOC entry 3393 (class 2604 OID 16426)
-- Name: negozio id; Type: DEFAULT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.negozio ALTER COLUMN id SET DEFAULT nextval('comicgalaxy.negozio_codice_seq'::regclass);


--
-- TOC entry 3400 (class 2604 OID 16526)
-- Name: ordine id; Type: DEFAULT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.ordine ALTER COLUMN id SET DEFAULT nextval('comicgalaxy.ordine_id_seq'::regclass);


--
-- TOC entry 3395 (class 2604 OID 16469)
-- Name: prodotto id; Type: DEFAULT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.prodotto ALTER COLUMN id SET DEFAULT nextval('comicgalaxy.prodotto_id_seq'::regclass);


--
-- TOC entry 3398 (class 2604 OID 16497)
-- Name: tessera id; Type: DEFAULT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.tessera ALTER COLUMN id SET DEFAULT nextval('comicgalaxy.tessera_id_seq'::regclass);


--
-- TOC entry 3619 (class 0 OID 16397)
-- Dependencies: 217
-- Data for Name: cliente; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.cliente VALUES ('RSSALC99A41F205X', 'Alice', 'Rossi', 'alice.rossi@example.it');
INSERT INTO comicgalaxy.cliente VALUES ('BNCBRN85B12M501Y', 'Bruno', 'Bianchi', 'bruno.bianchi@example.it');
INSERT INTO comicgalaxy.cliente VALUES ('VRDCRL01C55L219T', 'Carla', 'Verdi', 'carla.verdi@example.it');
INSERT INTO comicgalaxy.cliente VALUES ('FRRDVD93D10M082U', 'Davide', 'Ferri', 'davide.ferri@example.it');
INSERT INTO comicgalaxy.cliente VALUES ('MNTLNE90E62H501Q', 'Elena', 'Monti', 'elena.monti@example.it');


--
-- TOC entry 3637 (class 0 OID 16590)
-- Dependencies: 235
-- Data for Name: dettaglio_fattura; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.dettaglio_fattura VALUES (8, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (9, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (10, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (11, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (12, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (13, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (14, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (15, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (16, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (17, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (18, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (19, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (20, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (21, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (22, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (23, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (24, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (25, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (26, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (27, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (28, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (29, 1, 8.90, 10);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (30, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (31, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (32, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (34, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (35, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (36, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (37, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (39, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (40, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (41, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (42, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (43, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (44, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (45, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (46, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (47, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (48, 1, 8.90, 9);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (50, 1, 8.90, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (51, 1, 8.90, 3);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (51, 2, 7.50, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (51, 5, 10.50, 2);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (52, 1, 8.90, 3);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (52, 2, 7.50, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (52, 5, 10.50, 2);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (53, 1, 8.90, 3);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (53, 2, 7.50, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (53, 5, 10.50, 2);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (54, 1, 8.90, 3);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (54, 2, 7.50, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (54, 5, 10.50, 2);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (55, 1, 8.90, 3);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (55, 2, 7.50, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (55, 5, 10.50, 2);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (56, 1, 8.90, 3);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (56, 2, 7.50, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (56, 5, 10.50, 2);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (58, 1, 8.90, 3);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (58, 2, 7.50, 1);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (58, 3, 9.90, 2);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (59, 1, 1.00, 100);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (60, 1, 1.00, 100);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (61, 1, 1.00, 100);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (64, 1, 1.00, 100);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (65, 1, 1.00, 100);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (66, 1, 1.00, 100);
INSERT INTO comicgalaxy.dettaglio_fattura VALUES (67, 1, 8.90, 5);


--
-- TOC entry 3635 (class 0 OID 16556)
-- Dependencies: 233
-- Data for Name: dettaglio_ordini; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.dettaglio_ordini VALUES (50, 1, 50, 5.00);


--
-- TOC entry 3629 (class 0 OID 16475)
-- Dependencies: 227
-- Data for Name: fattura; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.fattura VALUES (2, '2025-11-05', 0, 89.90, 'RSSALC99A41F205X', 2);
INSERT INTO comicgalaxy.fattura VALUES (3, '2025-11-05', 0, 89.90, 'RSSALC99A41F205X', 2);
INSERT INTO comicgalaxy.fattura VALUES (6, '2025-11-05', 30, 89.90, 'RSSALC99A41F205X', 2);
INSERT INTO comicgalaxy.fattura VALUES (8, '2025-11-13', 0, 0.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (9, '2025-11-13', 0, 0.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (10, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (11, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (12, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (13, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (14, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (15, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (16, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (17, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (18, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (19, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (20, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (21, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (22, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (23, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (24, '2025-11-13', 0, 8.90, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (25, '2025-11-13', 0, 8.90, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (26, '2025-11-13', 0, 8.90, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (27, '2025-11-13', 0, 8.90, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (28, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (29, '2025-11-13', 0, 89.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (30, '2025-11-13', 0, 80.10, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (31, '2025-11-15', 0, 80.10, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (32, '2025-11-15', 0, 80.10, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (34, '2025-11-17', 5, 76.09, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (35, '2025-11-17', 5, 76.09, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (36, '2025-11-17', 5, 76.09, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (37, '2025-11-17', 5, 76.09, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (39, '2025-11-17', 5, 80.10, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (40, '2025-11-17', 0, 80.10, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (41, '2025-11-17', 5, 80.10, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (42, '2025-11-17', 5, 76.09, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (43, '2025-11-17', 5, 76.10, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (44, '2025-11-17', 5, 76.10, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (45, '2025-11-17', 5, 76.10, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (46, '2025-11-17', 5, 76.10, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (47, '2025-11-17', 5, 76.10, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (48, '2025-11-17', 5, 76.10, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (50, '2025-11-19', 0, 8.90, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (51, '2025-11-19', 0, 55.20, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (52, '2025-11-19', 0, 55.20, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (53, '2025-11-19', 5, 52.44, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (54, '2025-11-19', 5, 52.44, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (55, '2025-11-19', 5, 52.44, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (56, '2025-11-19', 0, 55.20, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (58, '2025-11-20', 0, 54.00, 'RSSALC99A41F205X', 1);
INSERT INTO comicgalaxy.fattura VALUES (59, '2025-11-20', 0, 100.00, 'RSSALC99A41F205X', 3);
INSERT INTO comicgalaxy.fattura VALUES (60, '2025-11-20', 0, 100.00, 'RSSALC99A41F205X', 3);
INSERT INTO comicgalaxy.fattura VALUES (61, '2025-11-20', 0, 100.00, 'RSSALC99A41F205X', 3);
INSERT INTO comicgalaxy.fattura VALUES (64, '2025-11-20', 0, 100.00, 'RSSALC99A41F205X', 3);
INSERT INTO comicgalaxy.fattura VALUES (65, '2025-11-20', 0, 100.00, 'RSSALC99A41F205X', 3);
INSERT INTO comicgalaxy.fattura VALUES (66, '2025-11-20', 0, 100.00, 'RSSALC99A41F205X', 3);
INSERT INTO comicgalaxy.fattura VALUES (67, '2025-11-20', 0, 44.50, 'RSSALC99A41F205X', 1);


--
-- TOC entry 3632 (class 0 OID 16512)
-- Dependencies: 230
-- Data for Name: fornitore; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.fornitore VALUES ('IT23456789012', '0112345678', 'fornitore.torino@example.com', 2, 'Piedmont Panel Provider');
INSERT INTO comicgalaxy.fornitore VALUES ('IT12345678901', '0612345678', 'fornitore.roma@example.com', 1, 'Roma Graphic Imports');
INSERT INTO comicgalaxy.fornitore VALUES ('IT34567890123', '0551234567', 'fornitore.firenze@example.com', 3, 'Toscana Comics Trade');
INSERT INTO comicgalaxy.fornitore VALUES ('IT45678901234', '051234567', 'fornitore.bologna@example.com', 4, 'Emilia Comics Network');


--
-- TOC entry 3638 (class 0 OID 16614)
-- Dependencies: 236
-- Data for Name: fornitura_fornitore; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT12345678901', 1, 1.00, 0);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT23456789012', 1, 2.00, 0);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT23456789012', 3, 2.00, 0);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT23456789012', 50, 3.00, 0);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT12345678901', 49, 3.99, 0);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT34567890123', 2, 2.00, 970);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT34567890123', 4, 2.00, 976);
INSERT INTO comicgalaxy.fornitura_fornitore VALUES ('IT12345678901', 50, 5.00, 0);


--
-- TOC entry 3636 (class 0 OID 16573)
-- Dependencies: 234
-- Data for Name: fornitura_negozio; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.fornitura_negozio VALUES (1, 2, 7.49, 30);
INSERT INTO comicgalaxy.fornitura_negozio VALUES (1, 5, 8.75, 40);
INSERT INTO comicgalaxy.fornitura_negozio VALUES (3, 1, 1.00, 9565);
INSERT INTO comicgalaxy.fornitura_negozio VALUES (1, 1, 5.99, 65);


--
-- TOC entry 3625 (class 0 OID 16459)
-- Dependencies: 223
-- Data for Name: indirizzo; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.indirizzo VALUES (1, 'Milano', 'Via Dante Alighieri 12');
INSERT INTO comicgalaxy.indirizzo VALUES (2, 'Roma', 'Via Appia Nuova 45');
INSERT INTO comicgalaxy.indirizzo VALUES (3, 'Torino', 'Corso Vittorio Emanuele II 98');
INSERT INTO comicgalaxy.indirizzo VALUES (4, 'Firenze', 'Via dei Calzaiuoli 27');
INSERT INTO comicgalaxy.indirizzo VALUES (5, 'Bologna', 'Via Indipendenza 66');
INSERT INTO comicgalaxy.indirizzo VALUES (6, 'Roma', 'Via dei Fornitori 12');
INSERT INTO comicgalaxy.indirizzo VALUES (7, 'Torino', 'Corso Fiume 45');
INSERT INTO comicgalaxy.indirizzo VALUES (8, 'Firenze', 'Via del Commercio 8');
INSERT INTO comicgalaxy.indirizzo VALUES (9, 'Bologna', 'Via Bologna 21');


--
-- TOC entry 3620 (class 0 OID 16407)
-- Dependencies: 218
-- Data for Name: manager; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.manager VALUES ('fabio.gallo@example.it', 'Fabio', 'Gallo');
INSERT INTO comicgalaxy.manager VALUES ('giulia.luna@example.it', 'Giulia', 'Luna');
INSERT INTO comicgalaxy.manager VALUES ('hugo.marini@example.it', 'Hugo', 'Marini');
INSERT INTO comicgalaxy.manager VALUES ('irene.sarti@example.it', 'Irene', 'Sarti');
INSERT INTO comicgalaxy.manager VALUES ('luca.moretti@example.it', 'Luca', 'Moretti');
INSERT INTO comicgalaxy.manager VALUES ('federico.falcone@comicgalaxy.it', 'Federico', 'Falcone');


--
-- TOC entry 3622 (class 0 OID 16423)
-- Dependencies: 220
-- Data for Name: negozio; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.negozio VALUES (3, 'ComicGalaxy Torino', '0118765432', 'hugo.marini@example.it', 3, NULL);
INSERT INTO comicgalaxy.negozio VALUES (4, 'ComicGalaxy Firenze', '0553344556', 'irene.sarti@example.it', 4, NULL);
INSERT INTO comicgalaxy.negozio VALUES (5, 'ComicGalaxy Bologna', '0516677889', 'luca.moretti@example.it', 5, NULL);
INSERT INTO comicgalaxy.negozio VALUES (2, 'ComicGalaxy Roma', '0612345678', 'giulia.luna@example.it', 2, '2025-10-11');
INSERT INTO comicgalaxy.negozio VALUES (1, 'ComicGalaxy Milano', '5839593092', 'federico.falcone@comicgalaxy.it', 1, NULL);


--
-- TOC entry 3623 (class 0 OID 16434)
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
INSERT INTO comicgalaxy.orario VALUES ('Lunedì', '09:00:00', '18:00:00', 1);
INSERT INTO comicgalaxy.orario VALUES ('Martedì', '09:00:00', '18:00:00', 1);
INSERT INTO comicgalaxy.orario VALUES ('Mercoledì', '09:00:00', '18:00:00', 1);
INSERT INTO comicgalaxy.orario VALUES ('Giovedì', '09:00:00', '18:00:00', 1);
INSERT INTO comicgalaxy.orario VALUES ('Venerdì', '09:00:00', '18:00:00', 1);
INSERT INTO comicgalaxy.orario VALUES ('Sabato', '10:00:00', '15:00:00', 1);
INSERT INTO comicgalaxy.orario VALUES ('Domenica', NULL, NULL, 1);


--
-- TOC entry 3634 (class 0 OID 16523)
-- Dependencies: 232
-- Data for Name: ordine; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.ordine VALUES (1, '2025-11-22', 1, 'IT12345678901', true, '2025-11-21');


--
-- TOC entry 3627 (class 0 OID 16466)
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


--
-- TOC entry 3631 (class 0 OID 16494)
-- Dependencies: 229
-- Data for Name: tessera; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.tessera VALUES (1, 4488, 'RSSALC99A41F205X', 1, '2030-11-05', '2025-10-30');


--
-- TOC entry 3618 (class 0 OID 16390)
-- Dependencies: 216
-- Data for Name: utente; Type: TABLE DATA; Schema: comicgalaxy; Owner: federico
--

INSERT INTO comicgalaxy.utente VALUES ('alice.rossi@example.it', '3351234567', 'P@ssw0rdAlice!');
INSERT INTO comicgalaxy.utente VALUES ('bruno.bianchi@example.it', '3479876543', 'BrunoSecure123');
INSERT INTO comicgalaxy.utente VALUES ('carla.verdi@example.it', '3382223334', 'Carla2025$$');
INSERT INTO comicgalaxy.utente VALUES ('davide.ferri@example.it', '3334445556', 'DavideFerri#1');
INSERT INTO comicgalaxy.utente VALUES ('elena.monti@example.it', '3391112223', 'ElenaMonti!Pass');
INSERT INTO comicgalaxy.utente VALUES ('fabio.gallo@example.it', '3467778889', 'FabioGallo*789');
INSERT INTO comicgalaxy.utente VALUES ('giulia.luna@example.it', '3285556660', 'GiuliaLuna_2025');
INSERT INTO comicgalaxy.utente VALUES ('hugo.marini@example.it', '3294443332', 'HugoMarini!Alpha');
INSERT INTO comicgalaxy.utente VALUES ('irene.sarti@example.it', '3348887771', 'IreneSarti&123');
INSERT INTO comicgalaxy.utente VALUES ('luca.moretti@example.it', '3312223334', 'LucaMoretti@2025');
INSERT INTO comicgalaxy.utente VALUES ('federico.falcone@comicgalaxy.it', '0123456789', 'capo');


--
-- TOC entry 3651 (class 0 OID 0)
-- Dependencies: 226
-- Name: fattura_id_seq; Type: SEQUENCE SET; Schema: comicgalaxy; Owner: federico
--

SELECT pg_catalog.setval('comicgalaxy.fattura_id_seq', 67, true);


--
-- TOC entry 3652 (class 0 OID 0)
-- Dependencies: 222
-- Name: indirizzo_id_seq; Type: SEQUENCE SET; Schema: comicgalaxy; Owner: federico
--

SELECT pg_catalog.setval('comicgalaxy.indirizzo_id_seq', 9, true);


--
-- TOC entry 3653 (class 0 OID 0)
-- Dependencies: 219
-- Name: negozio_codice_seq; Type: SEQUENCE SET; Schema: comicgalaxy; Owner: federico
--

SELECT pg_catalog.setval('comicgalaxy.negozio_codice_seq', 5, true);


--
-- TOC entry 3654 (class 0 OID 0)
-- Dependencies: 231
-- Name: ordine_id_seq; Type: SEQUENCE SET; Schema: comicgalaxy; Owner: federico
--

SELECT pg_catalog.setval('comicgalaxy.ordine_id_seq', 1, true);


--
-- TOC entry 3655 (class 0 OID 0)
-- Dependencies: 224
-- Name: prodotto_id_seq; Type: SEQUENCE SET; Schema: comicgalaxy; Owner: federico
--

SELECT pg_catalog.setval('comicgalaxy.prodotto_id_seq', 62, true);


--
-- TOC entry 3656 (class 0 OID 0)
-- Dependencies: 228
-- Name: tessera_id_seq; Type: SEQUENCE SET; Schema: comicgalaxy; Owner: federico
--

SELECT pg_catalog.setval('comicgalaxy.tessera_id_seq', 1, true);


--
-- TOC entry 3417 (class 2606 OID 16401)
-- Name: cliente cliente_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.cliente
    ADD CONSTRAINT cliente_pkey PRIMARY KEY (cf);


--
-- TOC entry 3437 (class 2606 OID 16561)
-- Name: dettaglio_ordini dettaglio_ordini_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.dettaglio_ordini
    ADD CONSTRAINT dettaglio_ordini_pkey PRIMARY KEY (id_prodotto, id_ordine);


--
-- TOC entry 3441 (class 2606 OID 16596)
-- Name: dettaglio_fattura fattura_negozio_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.dettaglio_fattura
    ADD CONSTRAINT fattura_negozio_pkey PRIMARY KEY (id_fattura, id_prodotto);


--
-- TOC entry 3429 (class 2606 OID 16482)
-- Name: fattura fattura_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fattura
    ADD CONSTRAINT fattura_pkey PRIMARY KEY (id);


--
-- TOC entry 3433 (class 2606 OID 16668)
-- Name: fornitore fornitore_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitore
    ADD CONSTRAINT fornitore_pkey PRIMARY KEY (p_iva);


--
-- TOC entry 3443 (class 2606 OID 16694)
-- Name: fornitura_fornitore fornitura_fornitore_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitura_fornitore
    ADD CONSTRAINT fornitura_fornitore_pkey PRIMARY KEY (p_iva_fornitore, id_prodotto);


--
-- TOC entry 3439 (class 2606 OID 16579)
-- Name: fornitura_negozio fornitura_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitura_negozio
    ADD CONSTRAINT fornitura_pkey PRIMARY KEY (id_negozio, id_prodotto);


--
-- TOC entry 3425 (class 2606 OID 16464)
-- Name: indirizzo indirizzo_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.indirizzo
    ADD CONSTRAINT indirizzo_pkey PRIMARY KEY (id);


--
-- TOC entry 3419 (class 2606 OID 16411)
-- Name: manager manager_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.manager
    ADD CONSTRAINT manager_pkey PRIMARY KEY (mail);


--
-- TOC entry 3421 (class 2606 OID 16428)
-- Name: negozio negozio_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.negozio
    ADD CONSTRAINT negozio_pkey PRIMARY KEY (id);


--
-- TOC entry 3423 (class 2606 OID 16807)
-- Name: orario orario_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.orario
    ADD CONSTRAINT orario_pkey PRIMARY KEY (giorno, id_negozio);


--
-- TOC entry 3435 (class 2606 OID 16528)
-- Name: ordine ordine_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.ordine
    ADD CONSTRAINT ordine_pkey PRIMARY KEY (id);


--
-- TOC entry 3427 (class 2606 OID 16473)
-- Name: prodotto prodotto_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.prodotto
    ADD CONSTRAINT prodotto_pkey PRIMARY KEY (id);


--
-- TOC entry 3431 (class 2606 OID 16501)
-- Name: tessera tessera_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.tessera
    ADD CONSTRAINT tessera_pkey PRIMARY KEY (id);


--
-- TOC entry 3415 (class 2606 OID 16396)
-- Name: utente utente_pkey; Type: CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.utente
    ADD CONSTRAINT utente_pkey PRIMARY KEY (mail);


--
-- TOC entry 3465 (class 2620 OID 16785)
-- Name: orario trg_check_orario; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trg_check_orario BEFORE INSERT OR UPDATE ON comicgalaxy.orario FOR EACH ROW EXECUTE FUNCTION comicgalaxy.check_orario_apertura_chiusura();


--
-- TOC entry 3468 (class 2620 OID 16666)
-- Name: dettaglio_ordini trigger_aggiorna_disponibilita_fornitore; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trigger_aggiorna_disponibilita_fornitore AFTER INSERT ON comicgalaxy.dettaglio_ordini FOR EACH ROW EXECUTE FUNCTION comicgalaxy.aggiorna_disponibilita_fornitore();


--
-- TOC entry 3466 (class 2620 OID 16709)
-- Name: fattura trigger_aggiorna_saldo_punti; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trigger_aggiorna_saldo_punti AFTER INSERT ON comicgalaxy.fattura FOR EACH ROW EXECUTE FUNCTION comicgalaxy.aggiorna_saldo_punti();


--
-- TOC entry 3464 (class 2620 OID 16783)
-- Name: negozio trigger_check_manager_disponibile; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trigger_check_manager_disponibile BEFORE INSERT OR UPDATE ON comicgalaxy.negozio FOR EACH ROW EXECUTE FUNCTION comicgalaxy.check_manager_disponibile();


--
-- TOC entry 3467 (class 2620 OID 16730)
-- Name: ordine trigger_ritiro; Type: TRIGGER; Schema: comicgalaxy; Owner: federico
--

CREATE TRIGGER trigger_ritiro AFTER UPDATE OF ritirato ON comicgalaxy.ordine FOR EACH ROW EXECUTE FUNCTION comicgalaxy.ritiro_ordine();


--
-- TOC entry 3444 (class 2606 OID 16402)
-- Name: cliente cliente_mail_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.cliente
    ADD CONSTRAINT cliente_mail_fkey FOREIGN KEY (mail) REFERENCES comicgalaxy.utente(mail) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3456 (class 2606 OID 16567)
-- Name: dettaglio_ordini dettaglio_ordini_id_ordine_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.dettaglio_ordini
    ADD CONSTRAINT dettaglio_ordini_id_ordine_fkey FOREIGN KEY (id_ordine) REFERENCES comicgalaxy.ordine(id);


--
-- TOC entry 3457 (class 2606 OID 16562)
-- Name: dettaglio_ordini dettaglio_ordini_id_prodotto_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.dettaglio_ordini
    ADD CONSTRAINT dettaglio_ordini_id_prodotto_fkey FOREIGN KEY (id_prodotto) REFERENCES comicgalaxy.prodotto(id);


--
-- TOC entry 3449 (class 2606 OID 16483)
-- Name: fattura fattura_cf_cliente_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fattura
    ADD CONSTRAINT fattura_cf_cliente_fkey FOREIGN KEY (cf_cliente) REFERENCES comicgalaxy.cliente(cf) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3450 (class 2606 OID 16488)
-- Name: fattura fattura_codice_negozio_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fattura
    ADD CONSTRAINT fattura_codice_negozio_fkey FOREIGN KEY (codice_negozio) REFERENCES comicgalaxy.negozio(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3460 (class 2606 OID 16597)
-- Name: dettaglio_fattura fattura_negozio_id_fattura_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.dettaglio_fattura
    ADD CONSTRAINT fattura_negozio_id_fattura_fkey FOREIGN KEY (id_fattura) REFERENCES comicgalaxy.fattura(id);


--
-- TOC entry 3461 (class 2606 OID 16602)
-- Name: dettaglio_fattura fattura_negozio_id_negozio_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.dettaglio_fattura
    ADD CONSTRAINT fattura_negozio_id_negozio_fkey FOREIGN KEY (id_prodotto) REFERENCES comicgalaxy.negozio(id);


--
-- TOC entry 3446 (class 2606 OID 16551)
-- Name: negozio fk_indirizzo; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.negozio
    ADD CONSTRAINT fk_indirizzo FOREIGN KEY (id_indirizzo) REFERENCES comicgalaxy.indirizzo(id);


--
-- TOC entry 3447 (class 2606 OID 16449)
-- Name: negozio fk_manager; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.negozio
    ADD CONSTRAINT fk_manager FOREIGN KEY (manager) REFERENCES comicgalaxy.manager(mail) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3453 (class 2606 OID 16517)
-- Name: fornitore fornitore_indirizzo_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitore
    ADD CONSTRAINT fornitore_indirizzo_fkey FOREIGN KEY (indirizzo) REFERENCES comicgalaxy.indirizzo(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3462 (class 2606 OID 16626)
-- Name: fornitura_fornitore fornitura_fornitore_id_prodotto_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitura_fornitore
    ADD CONSTRAINT fornitura_fornitore_id_prodotto_fkey FOREIGN KEY (id_prodotto) REFERENCES comicgalaxy.prodotto(id);


--
-- TOC entry 3463 (class 2606 OID 16695)
-- Name: fornitura_fornitore fornitura_fornitore_p_iva_fornitore_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitura_fornitore
    ADD CONSTRAINT fornitura_fornitore_p_iva_fornitore_fkey FOREIGN KEY (p_iva_fornitore) REFERENCES comicgalaxy.fornitore(p_iva);


--
-- TOC entry 3458 (class 2606 OID 16580)
-- Name: fornitura_negozio fornitura_id_negozio_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitura_negozio
    ADD CONSTRAINT fornitura_id_negozio_fkey FOREIGN KEY (id_negozio) REFERENCES comicgalaxy.negozio(id);


--
-- TOC entry 3459 (class 2606 OID 16585)
-- Name: fornitura_negozio fornitura_id_prodotto_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.fornitura_negozio
    ADD CONSTRAINT fornitura_id_prodotto_fkey FOREIGN KEY (id_prodotto) REFERENCES comicgalaxy.prodotto(id);


--
-- TOC entry 3445 (class 2606 OID 16412)
-- Name: manager manager_mail_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.manager
    ADD CONSTRAINT manager_mail_fkey FOREIGN KEY (mail) REFERENCES comicgalaxy.utente(mail) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3448 (class 2606 OID 16439)
-- Name: orario orario_codice_negozio_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.orario
    ADD CONSTRAINT orario_codice_negozio_fkey FOREIGN KEY (id_negozio) REFERENCES comicgalaxy.negozio(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3454 (class 2606 OID 16684)
-- Name: ordine ordine_fornitore_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.ordine
    ADD CONSTRAINT ordine_fornitore_fkey FOREIGN KEY (fornitore) REFERENCES comicgalaxy.fornitore(p_iva) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3455 (class 2606 OID 16529)
-- Name: ordine ordine_negozio_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.ordine
    ADD CONSTRAINT ordine_negozio_fkey FOREIGN KEY (negozio) REFERENCES comicgalaxy.negozio(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3451 (class 2606 OID 16502)
-- Name: tessera tessera_cf_cliente_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.tessera
    ADD CONSTRAINT tessera_cf_cliente_fkey FOREIGN KEY (cf_cliente) REFERENCES comicgalaxy.cliente(cf) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3452 (class 2606 OID 16507)
-- Name: tessera tessera_codice_negozio_fkey; Type: FK CONSTRAINT; Schema: comicgalaxy; Owner: federico
--

ALTER TABLE ONLY comicgalaxy.tessera
    ADD CONSTRAINT tessera_codice_negozio_fkey FOREIGN KEY (id_negozio) REFERENCES comicgalaxy.negozio(id) ON UPDATE CASCADE ON DELETE CASCADE;


-- Completed on 2025-11-22 10:25:31 CET

--
-- PostgreSQL database dump complete
--

\unrestrict hZifBaa36lgqT9aTGbtWOCU29fa63uCh4GtE4n6zrqQzlapUv6ujyNIto49k8TO

