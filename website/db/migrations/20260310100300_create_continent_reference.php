<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateContinentReference extends AbstractMigration {
    public function up(): void {
        $this->execute(<<<'SQL'
CREATE TABLE stats.continent_reference (
  country_alpha2 CHAR(2) PRIMARY KEY,
  continent_code CHAR(2) NOT NULL,
  continent_name VARCHAR(50) NOT NULL
);

COMMENT ON TABLE stats.continent_reference IS 'Maps ISO 3166-1 alpha-2 country codes to continent codes and names; 249 entries';
COMMENT ON COLUMN stats.continent_reference.continent_code IS 'AF=Africa, AN=Antarctica, AS=Asia, EU=Europe, NA=North America, OC=Oceania, SA=South America';
SQL
        );

        $this->execute(<<<'SQL'
INSERT INTO stats.continent_reference (country_alpha2, continent_code, continent_name) VALUES
('AO', 'AF', 'Africa'), ('BF', 'AF', 'Africa'), ('BI', 'AF', 'Africa'),
('BJ', 'AF', 'Africa'), ('BW', 'AF', 'Africa'), ('CD', 'AF', 'Africa'),
('CF', 'AF', 'Africa'), ('CG', 'AF', 'Africa'), ('CI', 'AF', 'Africa'),
('CM', 'AF', 'Africa'), ('CV', 'AF', 'Africa'), ('DJ', 'AF', 'Africa'),
('DZ', 'AF', 'Africa'), ('EG', 'AF', 'Africa'), ('EH', 'AF', 'Africa'),
('ER', 'AF', 'Africa'), ('ET', 'AF', 'Africa'), ('GA', 'AF', 'Africa'),
('GH', 'AF', 'Africa'), ('GM', 'AF', 'Africa'), ('GN', 'AF', 'Africa'),
('GQ', 'AF', 'Africa'), ('GW', 'AF', 'Africa'), ('KE', 'AF', 'Africa'),
('KM', 'AF', 'Africa'), ('LR', 'AF', 'Africa'), ('LS', 'AF', 'Africa'),
('LY', 'AF', 'Africa'), ('MA', 'AF', 'Africa'), ('MG', 'AF', 'Africa'),
('ML', 'AF', 'Africa'), ('MR', 'AF', 'Africa'), ('MU', 'AF', 'Africa'),
('MW', 'AF', 'Africa'), ('MZ', 'AF', 'Africa'), ('NA', 'AF', 'Africa'),
('NE', 'AF', 'Africa'), ('NG', 'AF', 'Africa'), ('RE', 'AF', 'Africa'),
('RW', 'AF', 'Africa'), ('SC', 'AF', 'Africa'), ('SD', 'AF', 'Africa'),
('SH', 'AF', 'Africa'), ('SL', 'AF', 'Africa'), ('SN', 'AF', 'Africa'),
('SO', 'AF', 'Africa'), ('SS', 'AF', 'Africa'), ('ST', 'AF', 'Africa'),
('SZ', 'AF', 'Africa'), ('TD', 'AF', 'Africa'), ('TG', 'AF', 'Africa'),
('TN', 'AF', 'Africa'), ('TZ', 'AF', 'Africa'), ('UG', 'AF', 'Africa'),
('YT', 'AF', 'Africa'), ('ZA', 'AF', 'Africa'), ('ZM', 'AF', 'Africa'),
('ZW', 'AF', 'Africa'),
('AQ', 'AN', 'Antarctica'), ('BV', 'AN', 'Antarctica'),
('GS', 'AN', 'Antarctica'), ('HM', 'AN', 'Antarctica'),
('TF', 'AN', 'Antarctica'),
('AE', 'AS', 'Asia'), ('AF', 'AS', 'Asia'), ('AM', 'AS', 'Asia'),
('AZ', 'AS', 'Asia'), ('BD', 'AS', 'Asia'), ('BH', 'AS', 'Asia'),
('BN', 'AS', 'Asia'), ('BT', 'AS', 'Asia'), ('CC', 'AS', 'Asia'),
('CN', 'AS', 'Asia'), ('CX', 'AS', 'Asia'), ('CY', 'AS', 'Asia'),
('GE', 'AS', 'Asia'), ('HK', 'AS', 'Asia'), ('ID', 'AS', 'Asia'),
('IL', 'AS', 'Asia'), ('IN', 'AS', 'Asia'), ('IO', 'AS', 'Asia'),
('IQ', 'AS', 'Asia'), ('IR', 'AS', 'Asia'), ('JO', 'AS', 'Asia'),
('JP', 'AS', 'Asia'), ('KG', 'AS', 'Asia'), ('KH', 'AS', 'Asia'),
('KP', 'AS', 'Asia'), ('KR', 'AS', 'Asia'), ('KW', 'AS', 'Asia'),
('KZ', 'AS', 'Asia'), ('LA', 'AS', 'Asia'), ('LB', 'AS', 'Asia'),
('LK', 'AS', 'Asia'), ('MM', 'AS', 'Asia'), ('MN', 'AS', 'Asia'),
('MO', 'AS', 'Asia'), ('MV', 'AS', 'Asia'), ('MY', 'AS', 'Asia'),
('NP', 'AS', 'Asia'), ('OM', 'AS', 'Asia'), ('PH', 'AS', 'Asia'),
('PK', 'AS', 'Asia'), ('PS', 'AS', 'Asia'), ('QA', 'AS', 'Asia'),
('SA', 'AS', 'Asia'), ('SG', 'AS', 'Asia'), ('SY', 'AS', 'Asia'),
('TH', 'AS', 'Asia'), ('TJ', 'AS', 'Asia'), ('TL', 'AS', 'Asia'),
('TM', 'AS', 'Asia'), ('TR', 'AS', 'Asia'), ('TW', 'AS', 'Asia'),
('UZ', 'AS', 'Asia'), ('VN', 'AS', 'Asia'), ('YE', 'AS', 'Asia'),
('AD', 'EU', 'Europe'), ('AL', 'EU', 'Europe'), ('AT', 'EU', 'Europe'),
('AX', 'EU', 'Europe'), ('BA', 'EU', 'Europe'), ('BE', 'EU', 'Europe'),
('BG', 'EU', 'Europe'), ('BY', 'EU', 'Europe'), ('CH', 'EU', 'Europe'),
('CZ', 'EU', 'Europe'), ('DE', 'EU', 'Europe'), ('DK', 'EU', 'Europe'),
('EE', 'EU', 'Europe'), ('ES', 'EU', 'Europe'), ('FI', 'EU', 'Europe'),
('FO', 'EU', 'Europe'), ('FR', 'EU', 'Europe'), ('GB', 'EU', 'Europe'),
('GG', 'EU', 'Europe'), ('GI', 'EU', 'Europe'), ('GR', 'EU', 'Europe'),
('HR', 'EU', 'Europe'), ('HU', 'EU', 'Europe'), ('IE', 'EU', 'Europe'),
('IM', 'EU', 'Europe'), ('IS', 'EU', 'Europe'), ('IT', 'EU', 'Europe'),
('JE', 'EU', 'Europe'), ('LI', 'EU', 'Europe'), ('LT', 'EU', 'Europe'),
('LU', 'EU', 'Europe'), ('LV', 'EU', 'Europe'), ('MC', 'EU', 'Europe'),
('MD', 'EU', 'Europe'), ('ME', 'EU', 'Europe'), ('MK', 'EU', 'Europe'),
('MT', 'EU', 'Europe'), ('NL', 'EU', 'Europe'), ('NO', 'EU', 'Europe'),
('PL', 'EU', 'Europe'), ('PT', 'EU', 'Europe'), ('RO', 'EU', 'Europe'),
('RS', 'EU', 'Europe'), ('RU', 'EU', 'Europe'), ('SE', 'EU', 'Europe'),
('SI', 'EU', 'Europe'), ('SJ', 'EU', 'Europe'), ('SK', 'EU', 'Europe'),
('SM', 'EU', 'Europe'), ('UA', 'EU', 'Europe'), ('VA', 'EU', 'Europe'),
('XK', 'EU', 'Europe'), ('AG', 'NA', 'North America'), ('AI', 'NA', 'North America'),
('AW', 'NA', 'North America'), ('BB', 'NA', 'North America'),
('BL', 'NA', 'North America'), ('BM', 'NA', 'North America'),
('BQ', 'NA', 'North America'), ('BS', 'NA', 'North America'),
('BZ', 'NA', 'North America'), ('CA', 'NA', 'North America'),
('CR', 'NA', 'North America'), ('CU', 'NA', 'North America'),
('CW', 'NA', 'North America'), ('DM', 'NA', 'North America'),
('DO', 'NA', 'North America'), ('GD', 'NA', 'North America'),
('GL', 'NA', 'North America'), ('GP', 'NA', 'North America'),
('GT', 'NA', 'North America'), ('HN', 'NA', 'North America'),
('HT', 'NA', 'North America'), ('JM', 'NA', 'North America'),
('KN', 'NA', 'North America'), ('KY', 'NA', 'North America'),
('LC', 'NA', 'North America'), ('MF', 'NA', 'North America'),
('MQ', 'NA', 'North America'), ('MS', 'NA', 'North America'),
('MX', 'NA', 'North America'), ('NI', 'NA', 'North America'),
('PA', 'NA', 'North America'), ('PM', 'NA', 'North America'),
('PR', 'NA', 'North America'), ('SV', 'NA', 'North America'),
('SX', 'NA', 'North America'), ('TC', 'NA', 'North America'),
('TT', 'NA', 'North America'), ('US', 'NA', 'North America'),
('VC', 'NA', 'North America'), ('VG', 'NA', 'North America'),
('VI', 'NA', 'North America'), ('AS', 'OC', 'Oceania'), ('AU', 'OC', 'Oceania'),
('CK', 'OC', 'Oceania'), ('FJ', 'OC', 'Oceania'), ('FM', 'OC', 'Oceania'),
('GU', 'OC', 'Oceania'), ('KI', 'OC', 'Oceania'), ('MH', 'OC', 'Oceania'),
('MP', 'OC', 'Oceania'), ('NC', 'OC', 'Oceania'), ('NF', 'OC', 'Oceania'),
('NR', 'OC', 'Oceania'), ('NU', 'OC', 'Oceania'), ('NZ', 'OC', 'Oceania'),
('PF', 'OC', 'Oceania'), ('PG', 'OC', 'Oceania'), ('PN', 'OC', 'Oceania'),
('PW', 'OC', 'Oceania'), ('SB', 'OC', 'Oceania'), ('TK', 'OC', 'Oceania'),
('TO', 'OC', 'Oceania'), ('TV', 'OC', 'Oceania'), ('VU', 'OC', 'Oceania'),
('WF', 'OC', 'Oceania'), ('WS', 'OC', 'Oceania'), ('AR', 'SA', 'South America'),
('BO', 'SA', 'South America'), ('BR', 'SA', 'South America'),
('CL', 'SA', 'South America'), ('CO', 'SA', 'South America'),
('EC', 'SA', 'South America'), ('FK', 'SA', 'South America'),
('GF', 'SA', 'South America'), ('GY', 'SA', 'South America'),
('PE', 'SA', 'South America'), ('PY', 'SA', 'South America'),
('SR', 'SA', 'South America'), ('UY', 'SA', 'South America'),
('VE', 'SA', 'South America')
ON CONFLICT (country_alpha2) DO NOTHING;
SQL
        );
    }

    public function down(): void {
        $this->execute('DROP TABLE IF EXISTS stats.continent_reference;');
    }
}
