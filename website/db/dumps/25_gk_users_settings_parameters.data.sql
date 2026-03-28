TRUNCATE geokrety.gk_users_settings_parameters CASCADE;
--
-- PostgreSQL database dump
--

\restrict 00GBLZutocozgCzbRLEIKITmCIouc1OmsIGQ9kwne2QYMa0qvcstcGt8MKFZiG8

-- Dumped from database version 16.13 (Ubuntu 16.13-1.pgdg24.04+1)
-- Dumped by pg_dump version 18.3 (Ubuntu 18.3-1.pgdg22.04+1)

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
-- Data for Name: gk_users_settings_parameters; Type: TABLE DATA; Schema: geokrety; Owner: -
--

SET SESSION AUTHORIZATION DEFAULT;

ALTER TABLE geokrety.gk_users_settings_parameters DISABLE TRIGGER ALL;

COPY geokrety.gk_users_settings_parameters (name, type, "default", description, created_on_datetime, updated_on_datetime) FROM stdin;
TRACKING_OPT_OUT	bool	false	Opt-out from site usage analytics	2023-04-05 09:22:25.452359+00	2023-04-05 09:22:25.452359+00
DISTANCE_UNIT	enum:metric|imperial	metric	Display distances unit (kilometers|miles)	2023-08-12 17:31:16.339082+00	2023-08-12 17:31:16.339082+00
DISPLAY_ABSOLUTE_DATE	bool	false	Display dates as absolute	2023-09-27 17:42:43.424446+00	2023-09-27 17:42:43.424446+00
HIDDEN_COMMENTS_REVEAL_ALL	bool	false	Show every comment that was marked hidden	2026-01-17 17:22:52.806606+00	2026-01-17 17:22:52.806606+00
HIDDEN_COMMENTS_REVEAL_OWNED_GEOKRETY	bool	true	Show hidden comments only for GeoKrety you own	2026-01-17 17:22:52.806606+00	2026-01-17 17:22:52.806606+00
INSTANT_NOTIFICATIONS	bool	false	Receive instant email notifications for GeoKret activities	2026-02-19 14:45:39.789593+00	2026-02-19 14:45:39.789593+00
DAILY_DIGEST	bool	false	Receive daily digest email of GeoKret activities	2026-02-19 14:45:39.789593+00	2026-02-19 14:45:39.789593+00
INSTANT_NOTIFICATIONS_MOVES_OWN_GK	bool	true	Receive instant notifications for moves of my own GeoKrety	2026-02-19 14:45:40.404985+00	2026-02-19 14:45:40.404985+00
INSTANT_NOTIFICATIONS_MOVES_WATCHED_GK	bool	true	Receive instant notifications for moves of GeoKrety I watch	2026-02-19 14:45:40.404985+00	2026-02-19 14:45:40.404985+00
INSTANT_NOTIFICATIONS_MOVES_AROUND_HOME	bool	true	Receive instant notifications for moves around my home location	2026-02-19 14:45:40.404985+00	2026-02-19 14:45:40.404985+00
INSTANT_NOTIFICATIONS_MOVE_COMMENTS	bool	true	Receive instant notifications for comments on moves	2026-02-19 14:45:40.404985+00	2026-02-19 14:45:40.404985+00
INSTANT_NOTIFICATIONS_LOVES	bool	true	Receive instant notifications when someone loves my GeoKrety	2026-02-20 14:28:18.446135+00	2026-02-20 14:28:18.446135+00
\.


ALTER TABLE geokrety.gk_users_settings_parameters ENABLE TRIGGER ALL;

--
-- PostgreSQL database dump complete
--

\unrestrict 00GBLZutocozgCzbRLEIKITmCIouc1OmsIGQ9kwne2QYMa0qvcstcGt8MKFZiG8

