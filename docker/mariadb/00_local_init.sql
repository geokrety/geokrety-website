-- used by local Makefile
-- first time only
CREATE DATABASE `geokrety-db`;
CREATE USER 'geokrety'@'%' IDENTIFIED BY PASSWORD '*A113ADC6C874A14802349DE0C02052CE6F1E7B6F';
GRANT ALL PRIVILEGES ON `geokrety-db`.* TO 'geokrety'@'localhost';
