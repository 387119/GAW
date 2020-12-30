SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;
COMMENT ON DATABASE gaw IS 'default administrative connection database';
CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;
COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';
SET search_path = public, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;
CREATE TABLE accounts (
    account_id bigint,
    acccount character varying,
    passwd character varying,
    password_hash character varying
);
ALTER TABLE accounts OWNER TO gaw;
CREATE TABLE accounts_bkp (
    account_id bigint,
    acccount character varying,
    passwd character varying,
    password_hash character varying
);
ALTER TABLE accounts_bkp OWNER TO gaw;
CREATE TABLE all_users (
    user_id character varying,
    user_name character varying,
    score bigint,
    alliance character varying,
    login character varying,
    password character varying,
    ulcheck boolean,
    error_code integer
);
ALTER TABLE all_users OWNER TO gaw;
CREATE TABLE all_users_copy (
    user_id character varying,
    user_name character varying,
    score bigint,
    alliance character varying,
    login character varying,
    password character varying,
    ulcheck boolean,
    error_code integer
);
ALTER TABLE all_users_copy OWNER TO gaw;
CREATE TABLE bot0 (
    user_name character varying,
    online boolean,
    last_update timestamp with time zone,
    offline_seconds integer
);
ALTER TABLE bot0 OWNER TO gaw;
CREATE TABLE bot1 (
    user_name character varying,
    last_save timestamp with time zone,
    res0 bigint,
    res1 bigint,
    res2 bigint,
    resmax bigint,
    comment character varying,
    last_update timestamp with time zone
);
ALTER TABLE bot1 OWNER TO gaw;
CREATE TABLE planets (
    planet_name character varying,
    "position" character varying NOT NULL,
    mother boolean,
    user_name character varying,
    last_list_update timestamp with time zone DEFAULT now(),
    last_detail_update timestamp with time zone,
    temp integer,
    size json,
    res json,
    power json,
    build json,
    spacecraft json,
    skin_id integer
);
ALTER TABLE planets OWNER TO gaw;
CREATE VIEW bot1_check AS
 SELECT planets.user_name,
    planets.skin_id,
    planets."position",
    planets.mother,
    to_number(((planets.res -> '0'::text))::text, '999999999999'::text) AS res0,
    to_number(((planets.res -> '1'::text))::text, '999999999999'::text) AS res1,
    to_number(((planets.res -> '2'::text))::text, '999999999999'::text) AS res2,
    to_number(((planets.build -> '7'::text))::text, '99'::text) AS hmet,
    to_number(((planets.build -> '8'::text))::text, '99'::text) AS hkris,
    to_number(((planets.build -> '9'::text))::text, '99'::text) AS hgaz,
    to_number(((planets.build -> '12'::text))::text, '99'::text) AS hbun,
    to_number(((planets.spacecraft -> '10'::text))::text, '999999999999'::text) AS zond
   FROM planets;
ALTER TABLE bot1_check OWNER TO gaw;
CREATE TABLE users (
    user_id bigint,
    user_name character varying,
    level integer,
    score integer,
    acccount character varying,
    device_id character varying,
    gold integer,
    enabled boolean DEFAULT true,
    type integer,
    owner character varying,
    server_id integer
);
ALTER TABLE users OWNER TO gaw;
CREATE VIEW bot1_work AS
 SELECT t1.user_name,
    t1.acccount,
    t1.planets_total,
    t1.planets_ready
   FROM ( SELECT u1.user_name,
            u1.acccount,
            ( SELECT count(*) AS count
                   FROM bot1_check b1
                  WHERE (((b1.hmet >= (8)::numeric) OR (b1.hkris >= (8)::numeric) OR (b1.hgaz >= (8)::numeric)) AND (b1.mother = false) AND (b1.hbun <> (1)::numeric) AND (b1.zond <> (8)::numeric) AND (b1.skin_id <> 56) AND ((b1.user_name)::text = (u1.user_name)::text))
                  GROUP BY b1.user_name) AS planets_total,
            ( SELECT count(*) AS count
                   FROM bot1_check b2
                  WHERE (((b2.hmet >= (8)::numeric) OR (b2.hkris >= (8)::numeric) OR (b2.hgaz >= (8)::numeric)) AND (b2.mother = false) AND (b2.hbun <> (1)::numeric) AND (b2.zond <> (8)::numeric) AND (b2.skin_id <> 56) AND ((b2.user_name)::text = (u1.user_name)::text) AND ((((b2.res0 + b2.res1) + b2.res2) > (1000000)::numeric) OR (b2.res2 > (200000)::numeric)))
                  GROUP BY b2.user_name) AS planets_ready
           FROM users u1
          WHERE ((u1.type = 2) AND (u1.enabled = true))) t1
  WHERE ((t1.planets_total = t1.planets_ready) AND (t1.planets_total IS NOT NULL) AND ((t1.user_name)::text IN ( SELECT bot0.user_name
           FROM bot0
          WHERE (bot0.online = false))));
ALTER TABLE bot1_work OWNER TO gaw;
CREATE VIEW bot1_work_raw AS
 SELECT t1.user_name,
    t1.acccount,
    t1.planets_total,
    t1.planets_ready,
    t1.last_list_update
   FROM ( SELECT u1.user_name,
            u1.acccount,
            ( SELECT count(*) AS count
                   FROM bot1_check b1
                  WHERE (((b1.hmet >= (8)::numeric) OR (b1.hkris >= (8)::numeric) OR (b1.hgaz >= (8)::numeric)) AND (b1.mother = false) AND (b1.hbun <> (1)::numeric) AND (b1.zond <> (8)::numeric) AND (b1.skin_id <> 56) AND ((b1.user_name)::text = (u1.user_name)::text))
                  GROUP BY b1.user_name) AS planets_total,
            COALESCE(( SELECT count(*) AS count
                   FROM bot1_check b2
                  WHERE (((b2.hmet >= (8)::numeric) OR (b2.hkris >= (8)::numeric) OR (b2.hgaz >= (8)::numeric)) AND (b2.mother = false) AND (b2.hbun <> (1)::numeric) AND (b2.zond <> (8)::numeric) AND (b2.skin_id <> 56) AND ((b2.user_name)::text = (u1.user_name)::text) AND ((((b2.res0 + b2.res1) + b2.res2) > (1000000)::numeric) OR (b2.res2 > (200000)::numeric)))
                  GROUP BY b2.user_name), (0)::bigint) AS planets_ready,
            ( SELECT max(p1.last_list_update) AS max
                   FROM planets p1
                  WHERE ((p1.user_name)::text = (u1.user_name)::text)) AS last_list_update
           FROM users u1
          WHERE ((u1.type = 2) AND (u1.enabled = true))) t1
  WHERE (t1.planets_total IS NOT NULL);
ALTER TABLE bot1_work_raw OWNER TO gaw;
CREATE VIEW bot2_res_raw AS
 SELECT planets.user_name,
    planets.skin_id,
    planets."position",
    planets.mother,
    (planets.res -> '0'::text) AS res_0,
    (planets.res -> '1'::text) AS res_1,
    (planets.res -> '2'::text) AS res_2
   FROM planets;
ALTER TABLE bot2_res_raw OWNER TO gaw;
CREATE VIEW bot2_res AS
 SELECT bot2_res_raw.user_name,
    bot2_res_raw.skin_id,
    bot2_res_raw."position",
    bot2_res_raw.mother,
    to_number((bot2_res_raw.res_0)::text, '999999999999'::text) AS res0,
    to_number((bot2_res_raw.res_2)::text, '999999999999'::text) AS res1,
    to_number((bot2_res_raw.res_2)::text, '999999999999'::text) AS res2
   FROM bot2_res_raw;
ALTER TABLE bot2_res OWNER TO gaw;
CREATE TABLE bot4 (
    user_name character varying,
    status integer,
    last_update timestamp with time zone,
    comment character varying
);
ALTER TABLE bot4 OWNER TO gaw;
CREATE TABLE builds (
    build_id integer,
    res0 integer,
    delta0 double precision,
    res1 integer,
    delta1 double precision,
    res2 integer,
    delta2 double precision,
    resk integer,
    deltak double precision
);
ALTER TABLE builds OWNER TO gaw;
CREATE TABLE builds_with_names (
    build_id integer,
    build_name character varying,
    res0 integer,
    delta0 double precision,
    res1 integer,
    delta1 double precision,
    res2 integer,
    delta2 double precision,
    resk integer,
    deltak double precision
);
ALTER TABLE builds_with_names OWNER TO gaw;
CREATE TABLE savers (
    user_name character varying,
    enabled boolean DEFAULT false,
    user_id bigint
);
ALTER TABLE savers OWNER TO gaw;
CREATE TABLE servers (
    server_id integer,
    name character varying,
    country character varying,
    state integer,
    new integer,
    address character varying
);
ALTER TABLE servers OWNER TO gaw;
CREATE TABLE userinfo (
    user_name character varying,
    tec json,
    item json,
    last_update timestamp with time zone
);
ALTER TABLE userinfo OWNER TO gaw;
CREATE TABLE users1 (
    user_id bigint,
    account_id bigint,
    server_id integer,
    user_name character varying,
    owner character varying,
    type integer,
    enabled boolean,
    level integer,
    score bigint,
    gold integer,
    device_id character varying,
    last_update timestamp with time zone
);
ALTER TABLE users1 OWNER TO gaw;
CREATE TABLE users_old (
    user_id bigint,
    user_name character varying,
    level integer,
    score integer,
    acccount character varying,
    device_id character varying,
    gold integer,
    enabled boolean,
    type integer,
    owner character varying,
    server_id integer
);
ALTER TABLE users_old OWNER TO gaw;
CREATE TABLE wiki_groups (
    gid integer NOT NULL,
    name character varying(50) NOT NULL
);
ALTER TABLE wiki_groups OWNER TO gaw;
CREATE SEQUENCE wiki_groups_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER TABLE wiki_groups_gid_seq OWNER TO gaw;
ALTER SEQUENCE wiki_groups_gid_seq OWNED BY wiki_groups.gid;
CREATE TABLE wiki_usergroup (
    uid integer NOT NULL,
    gid integer NOT NULL
);
ALTER TABLE wiki_usergroup OWNER TO gaw;
CREATE TABLE wiki_users (
    uid integer NOT NULL,
    login character varying(20) NOT NULL,
    pass character varying(255) NOT NULL,
    fullname character varying(255) DEFAULT ''::character varying NOT NULL,
    email character varying(255) DEFAULT ''::character varying NOT NULL
);
ALTER TABLE wiki_users OWNER TO gaw;
CREATE SEQUENCE wiki_users_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
ALTER TABLE wiki_users_uid_seq OWNER TO gaw;
ALTER SEQUENCE wiki_users_uid_seq OWNED BY wiki_users.uid;
ALTER TABLE ONLY wiki_groups ALTER COLUMN gid SET DEFAULT nextval('wiki_groups_gid_seq'::regclass);
ALTER TABLE ONLY wiki_users ALTER COLUMN uid SET DEFAULT nextval('wiki_users_uid_seq'::regclass);
ALTER TABLE ONLY accounts
    ADD CONSTRAINT accounts_acccount_key UNIQUE (acccount);
ALTER TABLE ONLY accounts
    ADD CONSTRAINT accounts_account_id_key UNIQUE (account_id);
ALTER TABLE ONLY bot0
    ADD CONSTRAINT bot0_user_name_key UNIQUE (user_name);
ALTER TABLE ONLY bot1
    ADD CONSTRAINT bot1_user_name_key UNIQUE (user_name);
ALTER TABLE ONLY bot4
    ADD CONSTRAINT bot4_user_name_key UNIQUE (user_name);
ALTER TABLE ONLY builds
    ADD CONSTRAINT builds_build_id_key UNIQUE (build_id);
ALTER TABLE ONLY planets
    ADD CONSTRAINT planets_position_key UNIQUE ("position");
ALTER TABLE ONLY savers
    ADD CONSTRAINT savers_user_name_key UNIQUE (user_name);
ALTER TABLE ONLY servers
    ADD CONSTRAINT servers_server_id_key UNIQUE (server_id);
ALTER TABLE ONLY userinfo
    ADD CONSTRAINT userinfo_user_name_key UNIQUE (user_name);
ALTER TABLE ONLY users1
    ADD CONSTRAINT users1_user_id_key UNIQUE (user_id);
ALTER TABLE ONLY users
    ADD CONSTRAINT users_user_name_key UNIQUE (user_name);
ALTER TABLE ONLY wiki_groups
    ADD CONSTRAINT wiki_groups_name_key UNIQUE (name);
ALTER TABLE ONLY wiki_groups
    ADD CONSTRAINT wiki_groups_pkey PRIMARY KEY (gid);
ALTER TABLE ONLY wiki_usergroup
    ADD CONSTRAINT wiki_usergroup_pkey PRIMARY KEY (uid, gid);
ALTER TABLE ONLY wiki_users
    ADD CONSTRAINT wiki_users_login_key UNIQUE (login);
ALTER TABLE ONLY wiki_users
    ADD CONSTRAINT wiki_users_pkey PRIMARY KEY (uid);
ALTER TABLE ONLY wiki_usergroup
    ADD CONSTRAINT wiki_usergroup_gid_fkey FOREIGN KEY (gid) REFERENCES wiki_groups(gid);
ALTER TABLE ONLY wiki_usergroup
    ADD CONSTRAINT wiki_usergroup_uid_fkey FOREIGN KEY (uid) REFERENCES wiki_users(uid);
