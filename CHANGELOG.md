### [2.14.6](https://github.com/geokrety/geokrety-website/compare/v2.14.5...v2.14.6) (2022-02-23)


### Dependencies

* Update composer.lock ([205fa72](https://github.com/geokrety/geokrety-website/commit/205fa72926dcacf41797bd196f5d6a164072acf9))

### [2.14.5](https://github.com/geokrety/geokrety-website/compare/v2.14.4...v2.14.5) (2022-02-23)


### Bug Fixes

* Fix dependency for sentry/sentry ([551cfeb](https://github.com/geokrety/geokrety-website/commit/551cfeb8612ea61d92f18558985f961d217897c1))

### [2.14.4](https://github.com/geokrety/geokrety-website/compare/v2.14.3...v2.14.4) (2022-02-19)


### Bug Fixes

* Fix DB migrator duplicates on gk_watched ([8230803](https://github.com/geokrety/geokrety-website/commit/823080394874def3fcd38608e080adad82760a24))


### Dependencies

* replace sentry/sdk by latest sentry/sentry ([be4cce3](https://github.com/geokrety/geokrety-website/commit/be4cce3d5de3463ae0abce48aff7ea850f7f6a68))

### [2.14.3](https://github.com/geokrety/geokrety-website/compare/v2.14.2...v2.14.3) (2022-02-17)


### Translations

* New Crowdin updates ([#710](https://github.com/geokrety/geokrety-website/issues/710)) ([d1cda79](https://github.com/geokrety/geokrety-website/commit/d1cda79620910dccf5f31bd04d3297b046ec4bab))

### [2.14.2](https://github.com/geokrety/geokrety-website/compare/v2.14.1...v2.14.2) (2022-01-31)


### Translations

* New Crowdin updates ([#708](https://github.com/geokrety/geokrety-website/issues/708)) ([bd74784](https://github.com/geokrety/geokrety-website/commit/bd747842897356ee23b9f8fbca5a68be0f55d65b))

### [2.14.1](https://github.com/geokrety/geokrety-website/compare/v2.14.0...v2.14.1) (2022-01-31)


### Bug Fixes

* Add password reset link on registration error ([468dff1](https://github.com/geokrety/geokrety-website/commit/468dff13cbf782a6391b32a355a6ae6544c90a0d))
* No need to translate emoji strings ([063c0b4](https://github.com/geokrety/geokrety-website/commit/063c0b4ea6fea1adfd62190491f10950dbbe6234))
* Social registration does not check for duplicate username ([92bb2ac](https://github.com/geokrety/geokrety-website/commit/92bb2accc8f62dc3ff789bcb8f70c3d813e134b5)), closes [#707](https://github.com/geokrety/geokrety-website/issues/707)

## [2.14.0](https://github.com/geokrety/geokrety-website/compare/v2.13.1...v2.14.0) (2022-01-30)


### Features

* Implement GeoKrety watch/unwatch/watchers ([62ecf71](https://github.com/geokrety/geokrety-website/commit/62ecf71c66f4c9e19a7869c8f233a0eb80ab7d91)), closes [#706](https://github.com/geokrety/geokrety-website/issues/706)


### Bug Fixes

* Fix database-migrator bug introduced by c66230b4 ([9415982](https://github.com/geokrety/geokrety-website/commit/9415982b7653475e7007069bef5926f2ac919dd7))
* Fix displaying some navbar items on mobile ([d9601a8](https://github.com/geokrety/geokrety-website/commit/d9601a81d32e1ca42b2b16f2010bc9b8097635b5))
* Fix importing move comments with links ([fa8752c](https://github.com/geokrety/geokrety-website/commit/fa8752c0bb67c519ef792bfa1b0e394d191514f6))

### [2.13.1](https://github.com/geokrety/geokrety-website/compare/v2.13.0...v2.13.1) (2022-01-29)


### Translations

* New Crowdin updates ([#704](https://github.com/geokrety/geokrety-website/issues/704)) ([56c0a44](https://github.com/geokrety/geokrety-website/commit/56c0a443611520faf01e6a482b035751c55c388d))

## [2.13.0](https://github.com/geokrety/geokrety-website/compare/v2.12.1...v2.13.0) (2022-01-29)


### Features

* Implemented search in navbar ([dccf8e0](https://github.com/geokrety/geokrety-website/commit/dccf8e0b941488c26a1770e5a12c7f5e5d133d9d)), closes [#364](https://github.com/geokrety/geokrety-website/issues/364)


### Bug Fixes

* Clear sensible env vars ([c66230b](https://github.com/geokrety/geokrety-website/commit/c66230b4f373d158bb8cadbdf2af19282c4d6e70))
* Display 404 instead of 403 ([6a2fc8a](https://github.com/geokrety/geokrety-website/commit/6a2fc8a56e226fc2c63358ba621c536156d0f9db))
* Fix highlight missing GeoKrety in lists ([bad359c](https://github.com/geokrety/geokrety-website/commit/bad359cd8e603531adfa9a3a505e950188b8f535))
* Fix pagination when param is % ([5866f32](https://github.com/geokrety/geokrety-website/commit/5866f325f7836ee296ca8f394fe7682f9e67c513))
* Fix posicon in lists ([647da55](https://github.com/geokrety/geokrety-website/commit/647da55c536996f41d4087deb89997f75d86629e))
* Use classic osm tiles ([73acd0a](https://github.com/geokrety/geokrety-website/commit/73acd0ab53b590f3a6575a317265484b2aedac00))


### Dependencies

* Upgrade xfra35/f3-access to v1.2.2 ([a3463f0](https://github.com/geokrety/geokrety-website/commit/a3463f0ba32fb08614967f236f5a97d368c8d87c))

### [2.12.1](https://github.com/geokrety/geokrety-website/compare/v2.12.0...v2.12.1) (2022-01-08)


### Translations

* New Crowdin updates ([#703](https://github.com/geokrety/geokrety-website/issues/703)) ([9b84fa8](https://github.com/geokrety/geokrety-website/commit/9b84fa8bbacc6177492ac36969d1f5a276399aee))

## [2.12.0](https://github.com/geokrety/geokrety-website/compare/v2.11.3...v2.12.0) (2022-01-08)


### Features

* Relates [#701](https://github.com/geokrety/geokrety-website/issues/701): manage home position in database ([9eca851](https://github.com/geokrety/geokrety-website/commit/9eca851d062787386b4346b0ae8e8b884c569fe0))
* Use logical colors to show observation area ([b9d1d0d](https://github.com/geokrety/geokrety-website/commit/b9d1d0d51e551bee43303e7d07b472052831c937))


### Bug Fixes

* Display message when observation area is disabled ([5a122f8](https://github.com/geokrety/geokrety-website/commit/5a122f8a4a0f983b9c09875c8af93e6e26149775))
* Fix links to help page ([11aca08](https://github.com/geokrety/geokrety-website/commit/11aca087e9897a970cda17bf162f8e049159e552))
* Hide missing GeoKrety from daily mails ([8cc9928](https://github.com/geokrety/geokrety-website/commit/8cc99284ee2880f5cc43c19d7883803d38e7e351))
* Hide missing GeoKrety from maps ([4b88bf0](https://github.com/geokrety/geokrety-website/commit/4b88bf0a9e381caa4b31d202166a2a1c0799de0e))
* Show move captcha only for unauthenticated users ([db9fde5](https://github.com/geokrety/geokrety-website/commit/db9fde56ca564236287ae53fa137128e3d94889e))
* User's home coordinates can be removed from account ([acff471](https://github.com/geokrety/geokrety-website/commit/acff471b2e61495bf2f96b79918b9920afc2921b)), closes [#701](https://github.com/geokrety/geokrety-website/issues/701)

### [2.11.3](https://github.com/geokrety/geokrety-website/compare/v2.11.2...v2.11.3) (2022-01-05)

### [2.11.2](https://github.com/geokrety/geokrety-website/compare/v2.11.1...v2.11.2) (2022-01-03)

### [2.11.1](https://github.com/geokrety/geokrety-website/compare/v2.11.0...v2.11.1) (2022-01-03)


### Bug Fixes

* Fix typo in many strings ([90d427e](https://github.com/geokrety/geokrety-website/commit/90d427e6dfe7fd650a6da46f1ca6bd673edef3bb))

## [2.11.0](https://github.com/geokrety/geokrety-website/compare/v2.10.1...v2.11.0) (2022-01-03)


### Features

* Redirect or display useful errors on 401/403/404 ([624031f](https://github.com/geokrety/geokrety-website/commit/624031f4b98dd0937bee77e78358c1c7f493bc1c))


### Bug Fixes

* Don't alert on locked script if they are acked ([5e5f6ba](https://github.com/geokrety/geokrety-website/commit/5e5f6ba9927de7a256fd6739510a4ce7cb9fca5c))
* Fix some corrupted modals ([68560ef](https://github.com/geokrety/geokrety-website/commit/68560ef5f998beb710f91860bfb58b6a8fedf606))
* Output /app-version as json ([6f144dc](https://github.com/geokrety/geokrety-website/commit/6f144dc6c101f38bded4e8e3dfd0ebeaa3de844a))

### [2.10.1](https://github.com/geokrety/geokrety-website/compare/v2.10.0...v2.10.1) (2021-12-26)


### Bug Fixes

* Fix new php-cs-issues since upgrade to 3.4 ([f931e7f](https://github.com/geokrety/geokrety-website/commit/f931e7fa7719bd0d4cd87169d3a6f9959e7b676b))

## [2.10.0](https://github.com/geokrety/geokrety-website/compare/v2.9.0...v2.10.0) (2021-12-26)


### Features

* Create manual action for crowdin upload ([05b02b9](https://github.com/geokrety/geokrety-website/commit/05b02b9c07123a07b10b219b2692a467f61cbfd5))


### Chores

* Drop duplicated release from Changelog ([ca8a1f2](https://github.com/geokrety/geokrety-website/commit/ca8a1f2d9a19e0f81cfb5a42d4e42a9592448146))

## [2.9.0](https://github.com/geokrety/geokrety-website/compare/v2.8.1...v2.9.0) (2021-12-26)


### Features

* Close all user's session on username change ([ce72033](https://github.com/geokrety/geokrety-website/commit/ce720332a246a1a3e36ed09176f0d90809e7507d))
* Enable csrf for admin ack script ([a01c089](https://github.com/geokrety/geokrety-website/commit/a01c0898006655c9fee64ce88311fa5ebf5c529d))
* Enable csrf for admin award user ([f9196a6](https://github.com/geokrety/geokrety-website/commit/f9196a66fd5272ea03721922cf111f616f2ff336))
* Enable csrf for admin invalidate user email ([723a266](https://github.com/geokrety/geokrety-website/commit/723a2666dd4a8ad65877590c444af3b2d7b1d13a))
* Enable csrf for admin unlock script ([90f38a3](https://github.com/geokrety/geokrety-website/commit/90f38a3386890970befbe88f10868013d3923cfc))
* Enable csrf for archive GeoKret ([a18a335](https://github.com/geokrety/geokrety-website/commit/a18a335528068e675201cf286988cd2b9f1fc574))
* Enable csrf for asend message to user ([595150f](https://github.com/geokrety/geokrety-website/commit/595150fd6b91d44838a757ec4e522b11d979b915))
* Enable csrf for change username ([053b1f7](https://github.com/geokrety/geokrety-website/commit/053b1f735677f5495a1b49bdb21cd57616922b6a))
* Enable csrf for changing preferred language ([c130a08](https://github.com/geokrety/geokrety-website/commit/c130a08a1f49f32ef0d7242f0b80d3c0b743625b))
* Enable csrf for define picture as main avatar ([96b9d7d](https://github.com/geokrety/geokrety-website/commit/96b9d7d794a965d4f3c0faaae752ce30fae853e9))
* Enable csrf for delete picture ([b154ec4](https://github.com/geokrety/geokrety-website/commit/b154ec4b353b2c058f7ddbdb32ebc3d15329aee6))
* Enable csrf for edit picture ([36f9c8c](https://github.com/geokrety/geokrety-website/commit/36f9c8c584f951ec2faff9308d6b1fd96be977c9))
* Enable csrf for email change revert ([6d87124](https://github.com/geokrety/geokrety-website/commit/6d871242d9b3121d2d8dacb2840013a239c16615))
* Enable csrf for email change token validate ([043ea35](https://github.com/geokrety/geokrety-website/commit/043ea355fcac84d1c63cbdf8b0cbbd2d5896eb76))
* Enable csrf for email change validate ([d3280dd](https://github.com/geokrety/geokrety-website/commit/d3280ddce5d28c5f919a1e6d5a5f998238d7e51a))
* Enable csrf for email revalidate ([7ea9db8](https://github.com/geokrety/geokrety-website/commit/7ea9db870b315ca710c4def6454a19801f2ee9cf))
* Enable csrf for GeoKret offer for adoption ([40b837f](https://github.com/geokrety/geokrety-website/commit/40b837f7560f916494caa64f8e5e0f13a79a9409))
* Enable csrf for geokrety claim ([bba97d5](https://github.com/geokrety/geokrety-website/commit/bba97d595600f5b265001be1bd4c1f919ddf2a36))
* Enable csrf for GeoKrety creation ([226390b](https://github.com/geokrety/geokrety-website/commit/226390bf82d884adc580d3f95a23e1bd617226a3))
* Enable csrf for GeoKrety label creation ([c38eec0](https://github.com/geokrety/geokrety-website/commit/c38eec0a90e65c0f8c8482586367e1fecced9a2d))
* Enable csrf for login forms ([bd5a7fd](https://github.com/geokrety/geokrety-website/commit/bd5a7fd89cbe6b0191423638949963facea21e4a))
* Enable csrf for move delete ([e9ef51a](https://github.com/geokrety/geokrety-website/commit/e9ef51ade429d833b49c6487f8904ce0a10ed05a))
* Enable csrf for move form ([d9a3c27](https://github.com/geokrety/geokrety-website/commit/d9a3c2756a420462000fa48a211990eba483a1be))
* Enable csrf for news comment delete ([7ea3209](https://github.com/geokrety/geokrety-website/commit/7ea32096c5772333b196c45ed3569c424c259f0b))
* Enable csrf for news comments ([cfc2e42](https://github.com/geokrety/geokrety-website/commit/cfc2e421bb3698906de1a6a22a0dcd8acdcb4b7e))
* Enable csrf for news subscription ([bdbe51b](https://github.com/geokrety/geokrety-website/commit/bdbe51bb16a6e1dd51caf8e2903b6d66ac7a3176))
* Enable csrf for OAuth disconnect ([001ac2f](https://github.com/geokrety/geokrety-website/commit/001ac2f824552b1c3a17b349880e559e17699ad8))
* Enable csrf for password change ([b48154b](https://github.com/geokrety/geokrety-website/commit/b48154b2fd212c5aff7d1e996a5bb1e68be04e49))
* Enable csrf for password recovery ([e72fbb9](https://github.com/geokrety/geokrety-website/commit/e72fbb97e309828d0d168a89ea5cb674dde062e8))
* Enable csrf for refresh secid ([40389a7](https://github.com/geokrety/geokrety-website/commit/40389a793f6035704a03309ec955348a690d6129))
* Enable csrf for registration forms ([389fe84](https://github.com/geokrety/geokrety-website/commit/389fe8473e67ae359e20f627e7e7e471023b076b))
* Enable csrf for term-of-use accept ([2e2403e](https://github.com/geokrety/geokrety-website/commit/2e2403ea905884e07dc7e3352792942542b67f53))
* Enable csrf for update email ([478845a](https://github.com/geokrety/geokrety-website/commit/478845ad2464b29aaca947901577c1c6670ef9be))
* Enable csrf for user banner template chooser ([305aca7](https://github.com/geokrety/geokrety-website/commit/305aca7142ee714e5d3cf5246f2e2518b0b2e549))
* Enable csrf for user define observation area ([d03e100](https://github.com/geokrety/geokrety-website/commit/d03e100c9043fe04a5dae9a48828d8ec823d8899))
* migrate extract translations strings to GH Actions ([911787a](https://github.com/geokrety/geokrety-website/commit/911787a58f4acb61d9b7390934768cc635f05a6d))


### Bug Fixes

* Add crowdin link in footer ([6978c7e](https://github.com/geokrety/geokrety-website/commit/6978c7e96284e1025b3497e03663b95d56b9144f))
* Use fresh database base schema ([0257a55](https://github.com/geokrety/geokrety-website/commit/0257a5561f1038e286a22e7598a3724a999fd1e4))


### Style

* Add some context typing ([145a0e7](https://github.com/geokrety/geokrety-website/commit/145a0e788c1f6ddf51d7a247e0168221f87a48e8))


### Code Refactoring

* Drop unnecessary code ([948a574](https://github.com/geokrety/geokrety-website/commit/948a57459f88b1aabb090c87a98c3687daa49283))
* Factorize checkCaptcha call ([858bd40](https://github.com/geokrety/geokrety-website/commit/858bd40afefc33304010da84bed48b30f5e225da))
* Factorize csrf inclusion ([c3b91d8](https://github.com/geokrety/geokrety-website/commit/c3b91d8f23071806e7b4718e72f1bedad03d6b63))

### [2.8.1](https://github.com/geokrety/geokrety-website/compare/v2.8.0...v2.8.1) (2021-12-09)

## [2.8.0](https://github.com/geokrety/geokrety-website/compare/v2.7.0...v2.8.0) (2021-12-07)


### Features

* Enable GH workflow ([903219b](https://github.com/geokrety/geokrety-website/commit/903219b114e34a6ca44d27dea47d18db0cf4b5c0))
