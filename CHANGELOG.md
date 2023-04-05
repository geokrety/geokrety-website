## [2.28.12](https://github.com/geokrety/geokrety-website/compare/v2.28.11...v2.28.12) (2023-04-05)


### Bug Fixes

* Remove static owner in create table ([9084166](https://github.com/geokrety/geokrety-website/commit/908416600fd3e705b0913ccd276986dfc21b2569))
* use env variable to select db in db-migrator ([62d87c7](https://github.com/geokrety/geokrety-website/commit/62d87c7525caf1a0cce91a18313f0aadff4bf17d))

## [2.28.11](https://github.com/geokrety/geokrety-website/compare/v2.28.10...v2.28.11) (2023-04-05)


### Bug Fixes

* Adjust field length in mass label generator ([d986e03](https://github.com/geokrety/geokrety-website/commit/d986e036503fc749509a79ab84ee2095598c9026))
* claim page need users to be authenticated ([a54e582](https://github.com/geokrety/geokrety-website/commit/a54e58263df053f8a4b70ddc684b576fe08ae21f))
* Hide mass labelling menu from unauthenticated users ([2f36369](https://github.com/geokrety/geokrety-website/commit/2f363693d619dc053542c159eefdb03136e5e0dc))
* Mass labelling page only available to authenticated users ([456f307](https://github.com/geokrety/geokrety-website/commit/456f30747d16f558612f5ab7be48d2af0c360f56))


### Translations

* New translations help.html (Portuguese) [skip translate] ([#817](https://github.com/geokrety/geokrety-website/issues/817)) ([136409d](https://github.com/geokrety/geokrety-website/commit/136409d4168713dcc70216145985feb8b22daac9))


### Chores

* move quantile to public schema ([8e7d06c](https://github.com/geokrety/geokrety-website/commit/8e7d06c31a325062afad90884f109500a76c1bd1))
* **deps:** bump cakephp/database from 4.4.0 to 4.4.10 ([#816](https://github.com/geokrety/geokrety-website/issues/816)) ([c7a01cc](https://github.com/geokrety/geokrety-website/commit/c7a01ccfd7a1bfc8098d8418f3d147f5f912c01a))
* Bump Sentry library version ([c34dbd9](https://github.com/geokrety/geokrety-website/commit/c34dbd916ddbb956982905ea4b77c9f9c3177577))

## [2.28.10](https://github.com/geokrety/geokrety-website/compare/v2.28.9...v2.28.10) (2023-02-25)


### Chores

* Fix pre-commit check ([d365677](https://github.com/geokrety/geokrety-website/commit/d36567732cfb54fe3697cef220b50223d760b742))

## [2.28.9](https://github.com/geokrety/geokrety-website/compare/v2.28.8...v2.28.9) (2023-02-25)


### Bug Fixes

* Split release workflows ([3a368fa](https://github.com/geokrety/geokrety-website/commit/3a368fa712bc1901702a3e1e75ab1e5fe49fb186))

## [2.28.8](https://github.com/geokrety/geokrety-website/compare/v2.28.7...v2.28.8) (2023-02-25)


### Bug Fixes

* correct workflow syntax ([71183b1](https://github.com/geokrety/geokrety-website/commit/71183b1a2a2a2bb19b321454c3f75f167e25b17d))
* create Sentry release on new tag ([ad5f2e4](https://github.com/geokrety/geokrety-website/commit/ad5f2e4a7b5526cacf866a302873709b860383d3))

## [2.28.7](https://github.com/geokrety/geokrety-website/compare/v2.28.6...v2.28.7) (2023-02-25)


### Bug Fixes

* Use github.ref to create sentry release ([251d07f](https://github.com/geokrety/geokrety-website/commit/251d07fef4ed967a5ee8fcbf34624646f8db1ae1))

## [2.28.6](https://github.com/geokrety/geokrety-website/compare/v2.28.5...v2.28.6) (2023-02-25)


### Bug Fixes

* Force create a new sentry release on new tags ([60b4ef1](https://github.com/geokrety/geokrety-website/commit/60b4ef11f2e40afbd004a11e3ed006a81328d61e))

## [2.28.5](https://github.com/geokrety/geokrety-website/compare/v2.28.4...v2.28.5) (2023-02-25)


### Bug Fixes

* id parameter no more accepted by GcHu export form ([6f60284](https://github.com/geokrety/geokrety-website/commit/6f6028402e9bc0b79f4e7acb51494375175ba28b))


### Translations

* New Crowdin updates ([#813](https://github.com/geokrety/geokrety-website/issues/813)) ([4267a0b](https://github.com/geokrety/geokrety-website/commit/4267a0be6822bf5758113c2bc8430de69145b829))

## [2.28.4](https://github.com/geokrety/geokrety-website/compare/v2.28.3...v2.28.4) (2023-01-04)


### Bug Fixes

* Hide "active dev" banner on prod ([ad868ea](https://github.com/geokrety/geokrety-website/commit/ad868eaaea9176ef4bc6575a7b486ae630a685c2))

## [2.28.3](https://github.com/geokrety/geokrety-website/compare/v2.28.2...v2.28.3) (2023-01-04)


### Bug Fixes

* Stop truncating setting table in db-migrator ([86b7ba5](https://github.com/geokrety/geokrety-website/commit/86b7ba540d80ed15328a60def95eb2f276f38f3b))

## [2.28.2](https://github.com/geokrety/geokrety-website/compare/v2.28.1...v2.28.2) (2023-01-03)


### Bug Fixes

* Add site stats refresh to cli ([2cca0a7](https://github.com/geokrety/geokrety-website/commit/2cca0a7340367c63b035de9be296de7246d5f87a))
* Enable OC DE waypoint sync ([0036c64](https://github.com/geokrety/geokrety-website/commit/0036c6400f40d59d0f404c4ac9be4e195dec4601))
* FIx a class not found error ([de941fe](https://github.com/geokrety/geokrety-website/commit/de941fec953b3b1fab4948a4f68f409dbc4472e0))
* Fix account activation link ([425fd99](https://github.com/geokrety/geokrety-website/commit/425fd99690a7b3df58c01cd166f0de08f5c69f79))

## [2.28.1](https://github.com/geokrety/geokrety-website/compare/v2.28.0...v2.28.1) (2023-01-03)


### Bug Fixes

* Add option to disable site analytics on registration ([0339b72](https://github.com/geokrety/geokrety-website/commit/0339b7209452ee27c30971813425e09a25e0c7a6))
* Disable OC RO sync temporarily ([7e4dae2](https://github.com/geokrety/geokrety-website/commit/7e4dae2b04b6359f96633f8c86dbb64313f4cb08))
* Restore table on db import ([91b85d4](https://github.com/geokrety/geokrety-website/commit/91b85d4812e27b07a4c009d7a5a755a7537a0431))

## [2.28.0](https://github.com/geokrety/geokrety-website/compare/v2.27.3...v2.28.0) (2023-01-02)


### Features

* Initial piwik integration ([5cc1dbe](https://github.com/geokrety/geokrety-website/commit/5cc1dbedffb505e78f2136f6e9490aaef3684f36))
* Render to GeoKrety travel line as gradient ([f793915](https://github.com/geokrety/geokrety-website/commit/f7939158afc29b5782992e797dec9214a9ac9f07))


### Bug Fixes

* Add new string to translation ([47076de](https://github.com/geokrety/geokrety-website/commit/47076de38d8da5f6eef48aef97b01c41af0a09cc))
* Add some Makefile commands ([7fdff08](https://github.com/geokrety/geokrety-website/commit/7fdff08bb07e82d037324877cabf46d37867defd))
* Align map markers with legend ([fbe737e](https://github.com/geokrety/geokrety-website/commit/fbe737e9e75b230c337066902335d56e0cb5356b)), closes [#808](https://github.com/geokrety/geokrety-website/issues/808)
* Disable GeoDashing synchronization ([ca7224a](https://github.com/geokrety/geokrety-website/commit/ca7224a526e40302aa239167891057ab49ea3c2f)), closes [#811](https://github.com/geokrety/geokrety-website/issues/811)
* Drop new tables in the db-migrator script ([11d3064](https://github.com/geokrety/geokrety-website/commit/11d30647d5809e6ce2ec665b6748347d22112d81))
* Fix first/last step detection on GeoKret details map ([8f6e385](https://github.com/geokrety/geokrety-website/commit/8f6e3853e48d37ca07a6dea2e04b8dd2dc20bc06))
* Fix moves numbering for "Show move on map" button ([55bf661](https://github.com/geokrety/geokrety-website/commit/55bf661062e70a83eea56abd037a1ad2f0aa4fef))
* Fix OKAPI importer ([b5f46cc](https://github.com/geokrety/geokrety-website/commit/b5f46ccd0668d695bf2ad042186825dd2bb570c1))
* Include MarkerLine only once in GeoJson ([28a5342](https://github.com/geokrety/geokrety-website/commit/28a5342af6eb3cacd4dfe6c898724b4ab0b22439)), closes [#808](https://github.com/geokrety/geokrety-website/issues/808)
* Resend Activation email if to update username and account not valid ([2e4e6d3](https://github.com/geokrety/geokrety-website/commit/2e4e6d375f02074c702310e69863005ec22f76cc))
* Review menu on mobile ([5598d78](https://github.com/geokrety/geokrety-website/commit/5598d78d386e1eca15993556c000151846b9f034))
* Rework users profile menu ([4a6af8c](https://github.com/geokrety/geokrety-website/commit/4a6af8ce378d85c4c3826b38228af9cb57da3883)), closes [#809](https://github.com/geokrety/geokrety-website/issues/809)
* Show the tracking opt-out on current user's profile only ([b5501e4](https://github.com/geokrety/geokrety-website/commit/b5501e4dee1ec7ac5d7ff15ed2d18dfee1400bc9))
* Use console writer in basex import All cli ([9df83c6](https://github.com/geokrety/geokrety-website/commit/9df83c6b61e1c8bb664ec59751568ce5fe204cb9))
* Use new c:geo logo in logbook ([7673d40](https://github.com/geokrety/geokrety-website/commit/7673d40b324e76b366d37ebb3eb13baf91e7b8cd)), closes [#810](https://github.com/geokrety/geokrety-website/issues/810)


### Reverts

* Revert "fix: Validate account while signin using OAuth" ([8ea9b99](https://github.com/geokrety/geokrety-website/commit/8ea9b99a85b0b508ddfc13b1d7ce438ac15df81f))


### Translations

* Add forgotten strings from translation ([42523d2](https://github.com/geokrety/geokrety-website/commit/42523d20a17ec1b642970c0f92088cc0ccce9822))


### Chores

* Add new Makefile entry to clean all ([01aa50e](https://github.com/geokrety/geokrety-website/commit/01aa50e5d14af81e8ab62014e986ed31baa86bbb))

## [2.27.3](https://github.com/geokrety/geokrety-website/compare/v2.27.2...v2.27.3) (2022-12-27)


### Bug Fixes

* Sent mail in foreground during unit tests ([95ecdeb](https://github.com/geokrety/geokrety-website/commit/95ecdebf818c9b6ca1b99f82740fad7a03fa60c0))

## [2.27.2](https://github.com/geokrety/geokrety-website/compare/v2.27.1...v2.27.2) (2022-12-27)


### Bug Fixes

* Move invalidate Account Activation to the database ([3431459](https://github.com/geokrety/geokrety-website/commit/3431459155673782c0f146cf56f146217ebd104e))
* Save the last sent account renotification datetime ([5b0d6b1](https://github.com/geokrety/geokrety-website/commit/5b0d6b1b6c41171f493174ae3dbd0165b150d30c))
* Send account activation mails in background ([9ccb63e](https://github.com/geokrety/geokrety-website/commit/9ccb63e562abb80590f445eb987bd2459cbb141e))
* Send welcome email on oauth login ([965bf1c](https://github.com/geokrety/geokrety-website/commit/965bf1cde1798faa398afb560b17109e83d28f30))
* Set reply-to to noreply on sent mails ([56f6ad9](https://github.com/geokrety/geokrety-website/commit/56f6ad981cee7fa31e032e3ecf67cb1528660c84))
* Validate account while signin using OAuth ([01e5f31](https://github.com/geokrety/geokrety-website/commit/01e5f3107ac09d891cf42c2d2df81f6d7b4b2261))


### Code Refactoring

* Declare a function argument type ([8bb3786](https://github.com/geokrety/geokrety-website/commit/8bb3786653baf4e4355f6abc204c9c678ed580ed))

## [2.27.1](https://github.com/geokrety/geokrety-website/compare/v2.27.0...v2.27.1) (2022-12-26)


### Bug Fixes

* Audit log for sent mails ([ff93340](https://github.com/geokrety/geokrety-website/commit/ff933407b8aa5af8adef29fd7b0149fac1a7d9df))
* Database integrety with deleted users on gk moves comments ([fbb510c](https://github.com/geokrety/geokrety-website/commit/fbb510c72caaf07116f0263aab49aabccc801cc9))
* Database integrity with deleted users on gk moves comments access ([f0cfb53](https://github.com/geokrety/geokrety-website/commit/f0cfb536d6fb7a6c033f250f6efe1fc1190a6007))
* Declare intenal IP ([22e5e6a](https://github.com/geokrety/geokrety-website/commit/22e5e6a3ee07a6d13ce66f86bbd884cfbc6e18ef))
* Prevent error when a picture have no author ([1e5772d](https://github.com/geokrety/geokrety-website/commit/1e5772d6bb1b85176c7d175adc2d66b3408b58d8))


### Performance Improvements

* Cache admin query info ([c7cb1bb](https://github.com/geokrety/geokrety-website/commit/c7cb1bb0e9fa2b5c6682cd7f6aeb380af609d807))

## [2.27.0](https://github.com/geokrety/geokrety-website/compare/v2.26.16...v2.27.0) (2022-12-24)


### Features

* Dump basex exports ([cd1d294](https://github.com/geokrety/geokrety-website/commit/cd1d294082c56df13416f48664515c4d5a75c1f0))

## [2.26.16](https://github.com/geokrety/geokrety-website/compare/v2.26.15...v2.26.16) (2022-12-24)


### Bug Fixes

* Crowdin as tag major ([7aa82f9](https://github.com/geokrety/geokrety-website/commit/7aa82f91a4d60049d8ef39a04578bd81366e001b))

## [2.26.15](https://github.com/geokrety/geokrety-website/compare/v2.26.14...v2.26.15) (2022-12-24)


### Bug Fixes

* Upload crowding translation files on branchs not tags ([611dfa2](https://github.com/geokrety/geokrety-website/commit/611dfa2077209c425f6177da5535e9639464ab2a))

## [2.26.14](https://github.com/geokrety/geokrety-website/compare/v2.26.13...v2.26.14) (2022-12-24)


### Bug Fixes

* Ci workflow adjustments ([991aa85](https://github.com/geokrety/geokrety-website/commit/991aa85873151048f15d93f7f72c8ba94392ae2d))

## [2.26.13](https://github.com/geokrety/geokrety-website/compare/v2.26.12...v2.26.13) (2022-12-24)


### Bug Fixes

* automatic branch rebase again ([f098b1e](https://github.com/geokrety/geokrety-website/commit/f098b1e2b95f3980bb75425386943fa7fd84a216))

## [2.26.12](https://github.com/geokrety/geokrety-website/compare/v2.26.11...v2.26.12) (2022-12-24)


### Bug Fixes

* Adjust automatic branche rebase ([41d1cb5](https://github.com/geokrety/geokrety-website/commit/41d1cb584491f066a7889b7b6fa2a2374efadae1))

## [2.26.11](https://github.com/geokrety/geokrety-website/compare/v2.26.10...v2.26.11) (2022-12-24)


### Bug Fixes

* Adjust tag name in workflow ([a67b5a8](https://github.com/geokrety/geokrety-website/commit/a67b5a8a06249aad8f2fd8eae85da7f5ed6c8e76))

## [2.26.10](https://github.com/geokrety/geokrety-website/compare/v2.26.9...v2.26.10) (2022-12-24)


### Translations

* New Crowdin updates ([#797](https://github.com/geokrety/geokrety-website/issues/797)) ([#800](https://github.com/geokrety/geokrety-website/issues/800)) ([d96a74d](https://github.com/geokrety/geokrety-website/commit/d96a74dc71dc9f7569e4b0831e7868bfd3ebfe47))
* New Crowdin updates ([#805](https://github.com/geokrety/geokrety-website/issues/805)) ([1b248a3](https://github.com/geokrety/geokrety-website/commit/1b248a323f40b2d3396a3985fb4ac68194e73e9c))


### Chores

* **deps:** bump guzzlehttp/guzzle from 7.4.3 to 7.5.0 ([#799](https://github.com/geokrety/geokrety-website/issues/799)) ([12bbeb6](https://github.com/geokrety/geokrety-website/commit/12bbeb6062b13694e391d45f2dc3d23be699583f))
* **deps:** bump smarty/smarty from 4.1.1 to 4.2.1 ([#798](https://github.com/geokrety/geokrety-website/issues/798)) ([3f44c62](https://github.com/geokrety/geokrety-website/commit/3f44c62f4e58b018150506c6d63960fae9893e19))

## [2.26.9](https://github.com/geokrety/geokrety-website/compare/v2.26.8...v2.26.9) (2022-12-24)


### Bug Fixes

* Fix an function call ([5e2c3aa](https://github.com/geokrety/geokrety-website/commit/5e2c3aa64e52531a13e7e3f5d11be8e6b4b4e334))

## [2.26.8](https://github.com/geokrety/geokrety-website/compare/v2.26.7...v2.26.8) (2022-12-24)


### Bug Fixes

* Prevent error when no script were run yet ([c83be03](https://github.com/geokrety/geokrety-website/commit/c83be033c933ccf330cf17d5c406d811f1528c7e))


### Chores

* Add link in admin home to wpt stats ([fff9062](https://github.com/geokrety/geokrety-website/commit/fff906267d2ccdc7fd471b8e8b6a082cc6428af8))
* Drop obsolete link in admin ([541c3ce](https://github.com/geokrety/geokrety-website/commit/541c3ce67f9fce11af8d6d9b0d2d039d26c3e9cd))

## [2.26.7](https://github.com/geokrety/geokrety-website/compare/v2.26.6...v2.26.7) (2022-12-24)


### Bug Fixes

* Disable amqp trigger while importing users ([09b4891](https://github.com/geokrety/geokrety-website/commit/09b489165a4e6fb9f65c1f7d3f6c9d7a985161c6))


### Chores

* Fix makefile entry ([ed9fc29](https://github.com/geokrety/geokrety-website/commit/ed9fc29c06c3ac360a619f99acede635b9b94b06))
* Reformat file ([dfb4e4d](https://github.com/geokrety/geokrety-website/commit/dfb4e4da173da3973aa9941325c3a2b22dcca33b))

## [2.26.6](https://github.com/geokrety/geokrety-website/compare/v2.26.5...v2.26.6) (2022-12-24)


### Bug Fixes

* Declare server IP in no req limits ([cf64d03](https://github.com/geokrety/geokrety-website/commit/cf64d03375e0f368a0c1b0b057390793624ca252))
* Declare whole ip range as not rate limited ([d84e08d](https://github.com/geokrety/geokrety-website/commit/d84e08d3ef4b44f8e8f048508c55734dff4752f6))
* Finish updating footer colors ([bbd28d3](https://github.com/geokrety/geokrety-website/commit/bbd28d3ba0610290bf7c280d1fee5daf1a4b0e72))
* Update support mail address ([8e556b0](https://github.com/geokrety/geokrety-website/commit/8e556b0d1a8569848b4736d21c31a7427a2b8b75))


### Chores

* Rename some Makefile entries ([52e9809](https://github.com/geokrety/geokrety-website/commit/52e98099262f9aac7695388ad69f5761e275d40a))

## [2.26.5](https://github.com/geokrety/geokrety-website/compare/v2.26.4...v2.26.5) (2022-12-23)


### Bug Fixes

* Shorten navbar links ([8c07795](https://github.com/geokrety/geokrety-website/commit/8c077951fcbdf154d8fd0f4771e117f654368e88))

## [2.26.4](https://github.com/geokrety/geokrety-website/compare/v2.26.3...v2.26.4) (2022-12-22)


### Bug Fixes

* Rename columns name ([67e6154](https://github.com/geokrety/geokrety-website/commit/67e61548c949160d7033936ce284db55d75939de))

## [2.26.3](https://github.com/geokrety/geokrety-website/compare/v2.26.2...v2.26.3) (2022-12-22)


### Translations

* New Crowdin updates ([#795](https://github.com/geokrety/geokrety-website/issues/795)) ([139afac](https://github.com/geokrety/geokrety-website/commit/139afac8bd78691eb2cdf57ca41e2f0201339ab3))

## [2.26.2](https://github.com/geokrety/geokrety-website/compare/v2.26.1...v2.26.2) (2022-12-22)


### Bug Fixes

* Dropzone was not displayed when hovered ([ceed920](https://github.com/geokrety/geokrety-website/commit/ceed920789e6c94931f918f79513054813726e59))

## [2.26.1](https://github.com/geokrety/geokrety-website/compare/v2.26.0...v2.26.1) (2022-12-16)


### Bug Fixes

* Add rabbitmq link into admin ([a194a9f](https://github.com/geokrety/geokrety-website/commit/a194a9fec7c661cca2189f90d7031d148dc05eda))


### Translations

* New Crowdin updates ([#794](https://github.com/geokrety/geokrety-website/issues/794)) ([96c81b9](https://github.com/geokrety/geokrety-website/commit/96c81b957891bd466b63e54bc520adb68f4e231e))

## [2.26.0](https://github.com/geokrety/geokrety-website/compare/v2.25.17...v2.26.0) (2022-12-16)


### Features

* Add necessary material to synchronize a BaseX copy ([223c13b](https://github.com/geokrety/geokrety-website/commit/223c13b896d4bdd728491ab1b07d094ff7ffa5d9))
* Add Postgres notifyQueues ([35a4eb1](https://github.com/geokrety/geokrety-website/commit/35a4eb1270d80c35c3f2b1770db1f4eb33dc7f36))
* Linking to RabbitMQ ([eacae70](https://github.com/geokrety/geokrety-website/commit/eacae70ef5e4bf2760d0275a1e6c22d240752122))

## [2.25.17](https://github.com/geokrety/geokrety-website/compare/v2.25.16...v2.25.17) (2022-12-16)


### Translations

* New Crowdin updates ([#790](https://github.com/geokrety/geokrety-website/issues/790)) ([d811a2e](https://github.com/geokrety/geokrety-website/commit/d811a2e184bf46dd167ae77f5474b4d443ce9ef7))
* New Crowdin updates ([#792](https://github.com/geokrety/geokrety-website/issues/792)) ([e18ea9f](https://github.com/geokrety/geokrety-website/commit/e18ea9fca0ad88dc20d49e46e7e2dac866893c21))

## [2.25.16](https://github.com/geokrety/geokrety-website/compare/v2.25.15...v2.25.16) (2022-12-14)


### Bug Fixes

* Show on home page the newly created GK with an owner ([a844dc5](https://github.com/geokrety/geokrety-website/commit/a844dc5845ea0b895184873c950e4f2b6df7faf4))

## [2.25.15](https://github.com/geokrety/geokrety-website/compare/v2.25.14...v2.25.15) (2022-12-14)


### Translations

* New translations messages.po (French) [skip translate] ([#789](https://github.com/geokrety/geokrety-website/issues/789)) ([3857716](https://github.com/geokrety/geokrety-website/commit/3857716c8d10cb421abe7827787f8d3d7b47c557))
* New translations messages.po (Portuguese) [skip translate] ([#786](https://github.com/geokrety/geokrety-website/issues/786)) ([8c218e3](https://github.com/geokrety/geokrety-website/commit/8c218e3f9e2ab0be62f1494e1a645e22cc946e1a))

## [2.25.14](https://github.com/geokrety/geokrety-website/compare/v2.25.13...v2.25.14) (2022-12-10)


### Translations

* New Crowdin updates ([#781](https://github.com/geokrety/geokrety-website/issues/781)) ([c08ac2b](https://github.com/geokrety/geokrety-website/commit/c08ac2bdeaa0cd769683f6ddd94e8a22fb0f8836))
* New Crowdin updates ([#783](https://github.com/geokrety/geokrety-website/issues/783)) ([2e38eb7](https://github.com/geokrety/geokrety-website/commit/2e38eb70d351163325c4032691f080f50022210c))
* New translations messages.po (French) [skip translate] ([#784](https://github.com/geokrety/geokrety-website/issues/784)) ([da90f76](https://github.com/geokrety/geokrety-website/commit/da90f76eaf7c4c6ddbed6ef2f68bbb09dac921cd))

## [2.25.13](https://github.com/geokrety/geokrety-website/compare/v2.25.12...v2.25.13) (2022-12-08)


### Translations

* New translations messages.po (Italian) [skip translate] ([#779](https://github.com/geokrety/geokrety-website/issues/779)) ([6fbed67](https://github.com/geokrety/geokrety-website/commit/6fbed67ba4ac6d14a816bf083f9982c3036e695e))

## [2.25.12](https://github.com/geokrety/geokrety-website/compare/v2.25.11...v2.25.12) (2022-12-08)


### Translations

* New Crowdin updates ([#778](https://github.com/geokrety/geokrety-website/issues/778)) ([e491698](https://github.com/geokrety/geokrety-website/commit/e491698045171dfa75849f5ba87c9bc8e78cb867))

## [2.25.11](https://github.com/geokrety/geokrety-website/compare/v2.25.10...v2.25.11) (2022-12-08)


### Translations

* New Crowdin updates ([#776](https://github.com/geokrety/geokrety-website/issues/776)) ([c0ed668](https://github.com/geokrety/geokrety-website/commit/c0ed66840d50068b91889bec58225d6c3d099513))

## [2.25.10](https://github.com/geokrety/geokrety-website/compare/v2.25.9...v2.25.10) (2022-12-08)


### Bug Fixes

* Fix typos ([0d30efb](https://github.com/geokrety/geokrety-website/commit/0d30efb8e02e0ec4fc2f81d356c050d8672dfd17))

## [2.25.9](https://github.com/geokrety/geokrety-website/compare/v2.25.8...v2.25.9) (2022-12-08)


### Bug Fixes

* Bump crowdin action ([c9723ec](https://github.com/geokrety/geokrety-website/commit/c9723ec3c4b36c4ea49694e8c1631fe00a831319))

## [2.25.8](https://github.com/geokrety/geokrety-website/compare/v2.25.7...v2.25.8) (2022-12-08)


### Bug Fixes

* dummy commit to trigger workflows ([3b216b7](https://github.com/geokrety/geokrety-website/commit/3b216b7382990aec028d1ba415a20ebb1ad2aa52))

## [2.25.7](https://github.com/geokrety/geokrety-website/compare/v2.25.6...v2.25.7) (2022-12-08)


### Translations

* New Crowdin updates ([#775](https://github.com/geokrety/geokrety-website/issues/775)) ([82a3b91](https://github.com/geokrety/geokrety-website/commit/82a3b91ff9799546ee63444c34a442d67681953e))

## [2.25.6](https://github.com/geokrety/geokrety-website/compare/v2.25.5...v2.25.6) (2022-12-08)


### Reverts

* Revert "ci: Change crowdin pull request title" ([0882472](https://github.com/geokrety/geokrety-website/commit/088247254e26e9bd6257a71781281345776bd2c2))

## [2.25.5](https://github.com/geokrety/geokrety-website/compare/v2.25.4...v2.25.5) (2022-12-08)


### Translations

* New Crowdin updates [skip translate] ([#773](https://github.com/geokrety/geokrety-website/issues/773)) ([5bf5e58](https://github.com/geokrety/geokrety-website/commit/5bf5e58afef93dee3132f6859e7f3c5aae8bb338))

## [2.25.4](https://github.com/geokrety/geokrety-website/compare/v2.25.3...v2.25.4) (2022-12-08)


### Translations

* New translations help.html (Chinese Simplified) [skip translate] ([cf4fb4c](https://github.com/geokrety/geokrety-website/commit/cf4fb4c363c662ca7f7d9750b127bb3987b59c5a))
* New translations help.html (Dutch) [skip translate] ([25ec404](https://github.com/geokrety/geokrety-website/commit/25ec404464a2812c6ad12486ffd1506042c4dc26))
* New translations help.html (French) [skip translate] ([faecec7](https://github.com/geokrety/geokrety-website/commit/faecec710c0c81c08cb7d7d69a3dc98e438861ce))
* New translations help.html (Italian) [skip translate] ([921bd09](https://github.com/geokrety/geokrety-website/commit/921bd0957ef2b33c1a5a5fdecdbdc6cdbde25e04))
* New translations help.html (Japanese) [skip translate] ([235beab](https://github.com/geokrety/geokrety-website/commit/235beab36538df4601ec94387202483e539e3108))
* New translations help.html (Polish) [skip translate] ([3b31212](https://github.com/geokrety/geokrety-website/commit/3b31212e167a22e43f5acbcf739465bb17f5a908))
* New translations help.html (Portuguese) [skip translate] ([07bae69](https://github.com/geokrety/geokrety-website/commit/07bae693f974d419b846dd5d63fce8cad327f8d6))
* New translations help.html (Spanish) [skip translate] ([bbf7a40](https://github.com/geokrety/geokrety-website/commit/bbf7a402c6847636c25bb17f4592de5676298ae6))
* New translations help.html (Thai) [skip translate] ([24b3e0f](https://github.com/geokrety/geokrety-website/commit/24b3e0f990df250ddcca55498569b6128c7965e1))

## [2.25.3](https://github.com/geokrety/geokrety-website/compare/v2.25.2...v2.25.3) (2022-12-07)


### Translations

* New Crowdin updates ([#766](https://github.com/geokrety/geokrety-website/issues/766)) ([c7e3f82](https://github.com/geokrety/geokrety-website/commit/c7e3f820ec5046b5cf50183a412d6ce28cd70db0))

## [2.25.2](https://github.com/geokrety/geokrety-website/compare/v2.25.1...v2.25.2) (2022-11-27)


### Translations

* New Crowdin updates ([#763](https://github.com/geokrety/geokrety-website/issues/763)) ([79416f0](https://github.com/geokrety/geokrety-website/commit/79416f0888bc0bc92e4b710d5ff67e459c06fd62))

## [2.25.1](https://github.com/geokrety/geokrety-website/compare/v2.25.0...v2.25.1) (2022-11-27)


### Bug Fixes

* Bump to postgres 14 ([56c2e29](https://github.com/geokrety/geokrety-website/commit/56c2e29887c50c392185e60cea1a65a0a45fee9d))


### Chores

* Fix typo ([54b0995](https://github.com/geokrety/geokrety-website/commit/54b099514ac426f13af84e9254b5cb0e4f046626))

## [2.25.0](https://github.com/geokrety/geokrety-website/compare/v2.24.1...v2.25.0) (2022-11-26)


### Features

* Allow bypass API count rate limiting ([0568444](https://github.com/geokrety/geokrety-website/commit/0568444b125949d394ac0a09a05e84a586dfd50a))
* No rate limit /s for internal communication ([132dbcc](https://github.com/geokrety/geokrety-website/commit/132dbcc65aec7704b123aff2d26e7921506db32c))


### Bug Fixes

* [cli] continue on error when a userbanne cannot be generated ([e50c9d6](https://github.com/geokrety/geokrety-website/commit/e50c9d6978f86ebe7d80c4984a529e4e837df39d))
* Also show current Rate-Limit when RL reached ([f9ea0dc](https://github.com/geokrety/geokrety-website/commit/f9ea0dc5dae11bd81296b48b7fbb525e6f72ea2c))
* Change admin menu color ([5a50293](https://github.com/geokrety/geokrety-website/commit/5a502934a33e005121264a879c29f1be1a469570))
* Exclude registration form from POST audit logs ([5f43e96](https://github.com/geokrety/geokrety-website/commit/5f43e96179c805b9c3dd82945baf88517fdae610))
* Fix user delete cascade ([aa62d02](https://github.com/geokrety/geokrety-website/commit/aa62d02d44b4ae11d50c7d77dad770b946bb8e8c))
* memory limit exception for "heavy" users ([7b900c5](https://github.com/geokrety/geokrety-website/commit/7b900c5dd8c62e58542f57d2014673dfe6e6a575)), closes [#761](https://github.com/geokrety/geokrety-website/issues/761)
* Prevent an export error when no comment added ([ecf6f85](https://github.com/geokrety/geokrety-website/commit/ecf6f85418fa19fe0f27cb4f5f884002a1ca6ac3))
* PrizeAwarderTopMovers restrict by move_type "alive" ([997c8df](https://github.com/geokrety/geokrety-website/commit/997c8df1eb5ab80443a7bd9165f69f39e27cc71d))
* Remove debug code ([16da80b](https://github.com/geokrety/geokrety-website/commit/16da80b896b1a04ff12bb54b75e7357af0dc8696))


### Code Refactoring

* rename constant ([deb7c48](https://github.com/geokrety/geokrety-website/commit/deb7c48bef153e2929b8bb184b2d9d7cc48615b4))

## [2.24.1](https://github.com/geokrety/geokrety-website/compare/v2.24.0...v2.24.1) (2022-10-16)


### Translations

* New Crowdin updates ([#757](https://github.com/geokrety/geokrety-website/issues/757)) ([904cc31](https://github.com/geokrety/geokrety-website/commit/904cc311c124539eb1fa962312bcf2ca565df334))

## [2.24.0](https://github.com/geokrety/geokrety-website/compare/v2.23.19...v2.24.0) (2022-10-16)


### Features

* Add GeoKrety "Country Track" ([07822f4](https://github.com/geokrety/geokrety-website/commit/07822f441c68b0382fa104da6f3b1c8e49663a92)), closes [#608](https://github.com/geokrety/geokrety-website/issues/608)

## [2.23.19](https://github.com/geokrety/geokrety-website/compare/v2.23.18...v2.23.19) (2022-10-16)


### Bug Fixes

* Limit geojson moves to 500 results ([cc37a2f](https://github.com/geokrety/geokrety-website/commit/cc37a2fe074202e6241e5fd235573206154b6ce9))

## [2.23.18](https://github.com/geokrety/geokrety-website/compare/v2.23.17...v2.23.18) (2022-10-16)


### Bug Fixes

* Warkaround map pins issue with multiple pages ([1499551](https://github.com/geokrety/geokrety-website/commit/1499551c7f4c4ca7fb479f871339187331207c46))

## [2.23.17](https://github.com/geokrety/geokrety-website/compare/v2.23.16...v2.23.17) (2022-10-15)


### Bug Fixes

* Account created via OAuth have no password ([db9e851](https://github.com/geokrety/geokrety-website/commit/db9e851857081a5e27c03a0306d08a23d6af4fc7)), closes [#755](https://github.com/geokrety/geokrety-website/issues/755)
* Add dev link to create social auth connect users ([ede3e21](https://github.com/geokrety/geokrety-website/commit/ede3e21bbbfbeaf7688202d9265862fb95fbd0ac))

## [2.23.16](https://github.com/geokrety/geokrety-website/compare/v2.23.15...v2.23.16) (2022-10-14)


### Translations

* New Crowdin updates ([#753](https://github.com/geokrety/geokrety-website/issues/753)) ([cf22659](https://github.com/geokrety/geokrety-website/commit/cf2265935fb5b9a9b8ea557fd58f51057c46079b))

## [2.23.15](https://github.com/geokrety/geokrety-website/compare/v2.23.14...v2.23.15) (2022-10-14)


### Bug Fixes

* Fix Visual images in QA Tests ([c869c1f](https://github.com/geokrety/geokrety-website/commit/c869c1fec742712c2454f367a3c06ca3df68b8bb))

## [2.23.14](https://github.com/geokrety/geokrety-website/compare/v2.23.13...v2.23.14) (2022-10-14)


### Bug Fixes

* Fix way to set mino bucket public ([#751](https://github.com/geokrety/geokrety-website/issues/751)) ([cea1ad4](https://github.com/geokrety/geokrety-website/commit/cea1ad4ae0b98fd8b96443df6c32fd5b2b5a7bf1))

## [2.23.13](https://github.com/geokrety/geokrety-website/compare/v2.23.12...v2.23.13) (2022-10-14)


### Translations

* New Crowdin updates ([#750](https://github.com/geokrety/geokrety-website/issues/750)) ([4ea2829](https://github.com/geokrety/geokrety-website/commit/4ea2829b013a2305c7d67e428e11285bc8f9b601))

## [2.23.12](https://github.com/geokrety/geokrety-website/compare/v2.23.11...v2.23.12) (2022-10-14)


### Bug Fixes

* Fix expurge job ([#749](https://github.com/geokrety/geokrety-website/issues/749)) ([9adc93f](https://github.com/geokrety/geokrety-website/commit/9adc93ff3c1ca8774d07c77ce24019b1b59d39a1))

## [2.23.11](https://github.com/geokrety/geokrety-website/compare/v2.23.10...v2.23.11) (2022-09-18)


### Translations

* New Crowdin updates ([#743](https://github.com/geokrety/geokrety-website/issues/743)) ([d4e6fbe](https://github.com/geokrety/geokrety-website/commit/d4e6fbe3e5242ed42f633eae55a6f4d2766bf025))

## [2.23.10](https://github.com/geokrety/geokrety-website/compare/v2.23.9...v2.23.10) (2022-06-26)


### Bug Fixes

* Prevent horizontal scroll with datatable ([628ad20](https://github.com/geokrety/geokrety-website/commit/628ad205d3454ae6af6a0a6e5da047922ddbb261))

## [2.23.9](https://github.com/geokrety/geokrety-website/compare/v2.23.8...v2.23.9) (2022-06-26)


### Translations

* New Crowdin updates ([#730](https://github.com/geokrety/geokrety-website/issues/730)) ([175af1f](https://github.com/geokrety/geokrety-website/commit/175af1f1322d4a7e46669124e45c2c61725846e1))

## [2.23.8](https://github.com/geokrety/geokrety-website/compare/v2.23.7...v2.23.8) (2022-06-26)


### Bug Fixes

* Create initial custom error pages ([8ca64c0](https://github.com/geokrety/geokrety-website/commit/8ca64c0d9081aa00ea5362e0a865d8cdba0f9774)), closes [#734](https://github.com/geokrety/geokrety-website/issues/734)
* Define quick search input max length ([296ce64](https://github.com/geokrety/geokrety-website/commit/296ce6476eabee0a39fe72ac7a49a9338f073c33)), closes [#734](https://github.com/geokrety/geokrety-website/issues/734)

## [2.23.7](https://github.com/geokrety/geokrety-website/compare/v2.23.6...v2.23.7) (2022-06-26)


### Bug Fixes

* Fix datetime format in create move form ([f27ce13](https://github.com/geokrety/geokrety-website/commit/f27ce13ab48eb4f8932126542214755cb0cfefb2)), closes [#740](https://github.com/geokrety/geokrety-website/issues/740)
* js variable redefinition ([f9d66b3](https://github.com/geokrety/geokrety-website/commit/f9d66b3fd508221a19794edb12339bc9e3eb9e83))

## [2.23.6](https://github.com/geokrety/geokrety-website/compare/v2.23.5...v2.23.6) (2022-06-26)


### Bug Fixes

* Message for email revalidation was not localized ([63c7906](https://github.com/geokrety/geokrety-website/commit/63c7906b89ca51bbd4d70ac521f98c4509bde0c1)), closes [#739](https://github.com/geokrety/geokrety-website/issues/739)

## [2.23.5](https://github.com/geokrety/geokrety-website/compare/v2.23.4...v2.23.5) (2022-06-26)


### Bug Fixes

* Allow non production and non dev instances to send emails ([1c01e4d](https://github.com/geokrety/geokrety-website/commit/1c01e4d97c2ec4b22f5401b43fb6a8c3a72e9c56)), closes [#738](https://github.com/geokrety/geokrety-website/issues/738)

## [2.23.4](https://github.com/geokrety/geokrety-website/compare/v2.23.3...v2.23.4) (2022-06-26)


### Bug Fixes

* Clear css on startup so they are regenerated ([fdf1d2e](https://github.com/geokrety/geokrety-website/commit/fdf1d2e423e24c1be8d9aea792e2634ac63eaa26))

## [2.23.3](https://github.com/geokrety/geokrety-website/compare/v2.23.2...v2.23.3) (2022-06-26)


### Bug Fixes

* Define bootstrap style for images added as inline in markdown ([de61b21](https://github.com/geokrety/geokrety-website/commit/de61b218187fb57d02490757d8b1ac7cd3c9df93)), closes [#735](https://github.com/geokrety/geokrety-website/issues/735)
* Fix editing GK ([0385090](https://github.com/geokrety/geokrety-website/commit/03850909f867ec00c0577e09119f6458126af5b6))
* Only owner can edit his GeoKrety ([f2bbd9a](https://github.com/geokrety/geokrety-website/commit/f2bbd9ab2b233650bfe4f20fc79735c58a3eefd6))
* Remove altitude profile placeholder as not really implemented ([08df211](https://github.com/geokrety/geokrety-website/commit/08df21147b381e82532a766e0e43004c005d3125)), closes [#737](https://github.com/geokrety/geokrety-website/issues/737)
* Remove external images from user to user messages ([72cbfd9](https://github.com/geokrety/geokrety-website/commit/72cbfd91bc4cfdcc3aab12aad24e7ffa29775163))
* Stay on current step while data are invalid ([2930209](https://github.com/geokrety/geokrety-website/commit/2930209a1566325cd3904a2f5081b21872501c85)), closes [#731](https://github.com/geokrety/geokrety-website/issues/731) [#732](https://github.com/geokrety/geokrety-website/issues/732) [#733](https://github.com/geokrety/geokrety-website/issues/733)

## [2.23.2](https://github.com/geokrety/geokrety-website/compare/v2.23.1...v2.23.2) (2022-06-25)


### Bug Fixes

* Don't let inscrybmde autoload resources from external ([3e5c6b0](https://github.com/geokrety/geokrety-website/commit/3e5c6b084af292cc5408dea4035c712f15aafcfc))
* Fix move comment lenght validation ([6bcf92d](https://github.com/geokrety/geokrety-website/commit/6bcf92d6d1371276740a4607408c84955678f1a4))

## [2.23.1](https://github.com/geokrety/geokrety-website/compare/v2.23.0...v2.23.1) (2022-06-25)


### Bug Fixes

* Also add DELETE support for local dev in docker-compose ([07082e7](https://github.com/geokrety/geokrety-website/commit/07082e7b30afef657426f59c783266bdaa216822))

## [2.23.0](https://github.com/geokrety/geokrety-website/compare/v2.22.9...v2.23.0) (2022-06-25)


### Features

* Implement full exports ([852666f](https://github.com/geokrety/geokrety-website/commit/852666faa5931b5d6c274300d2d30e77e042a1f2))


### Bug Fixes

* Repair image processing failure detection ([0f6d9f6](https://github.com/geokrety/geokrety-website/commit/0f6d9f62e2bfac2d0f5605eefb84b24761f43694)), closes [#742](https://github.com/geokrety/geokrety-website/issues/742)

## [2.22.9](https://github.com/geokrety/geokrety-website/compare/v2.22.8...v2.22.9) (2022-06-19)


### Bug Fixes

* Change observation area when locale numeric separator is not a . ([73d52f1](https://github.com/geokrety/geokrety-website/commit/73d52f17b98fe293a824faf2a865b13c17e71e13)), closes [#728](https://github.com/geokrety/geokrety-website/issues/728)

## [2.22.8](https://github.com/geokrety/geokrety-website/compare/v2.22.7...v2.22.8) (2022-06-19)


### Bug Fixes

* Fix: disconnect early in xml exports ([8e62b1a](https://github.com/geokrety/geokrety-website/commit/8e62b1adb4f36dbb8d4bd8abbb5d42d8a8825f93))
* handle legacy GKM route gk details by modifiedsince ([167a760](https://github.com/geokrety/geokrety-website/commit/167a76074efafc854f60602ed4f6eaec219a8184))

## [2.22.7](https://github.com/geokrety/geokrety-website/compare/v2.22.6...v2.22.7) (2022-06-19)


### Bug Fixes

* Disconnect early in xml exports ([009dfd2](https://github.com/geokrety/geokrety-website/commit/009dfd2c25fcf0e454cc31b541ddb56a4fdc3959))
* Reset rate limits in integration tests ([f560a90](https://github.com/geokrety/geokrety-website/commit/f560a90804d5f94f6603c4e68cdc8ccafc1ee741))

## [2.22.6](https://github.com/geokrety/geokrety-website/compare/v2.22.5...v2.22.6) (2022-06-19)


### Bug Fixes

* Fix reset development database ([10a15fa](https://github.com/geokrety/geokrety-website/commit/10a15fa37f93628ae2b419fcf66ff1a122cc8eb5))

## [2.22.5](https://github.com/geokrety/geokrety-website/compare/v2.22.4...v2.22.5) (2022-06-19)


### Bug Fixes

* Fix move audit sequence to audit schema ([2f1a94b](https://github.com/geokrety/geokrety-website/commit/2f1a94bc058d0eb6685cb2632ed113c6f19fe56d))


### Translations

* New Crowdin updates ([#729](https://github.com/geokrety/geokrety-website/issues/729)) ([b45761e](https://github.com/geokrety/geokrety-website/commit/b45761eced092c8ed8794759de68697dfa165833))

## [2.22.4](https://github.com/geokrety/geokrety-website/compare/v2.22.3...v2.22.4) (2022-06-18)


### Bug Fixes

* Move audit sequence to audit schema ([273ec37](https://github.com/geokrety/geokrety-website/commit/273ec37d9956b50c310114efd72a7ff66ecdc098))

## [2.22.3](https://github.com/geokrety/geokrety-website/compare/v2.22.2...v2.22.3) (2022-06-18)


### Translations

* New Crowdin updates ([#727](https://github.com/geokrety/geokrety-website/issues/727)) ([5f2dc2a](https://github.com/geokrety/geokrety-website/commit/5f2dc2ad88f20c520bb060723788ce1710251ab3))

## [2.22.2](https://github.com/geokrety/geokrety-website/compare/v2.22.1...v2.22.2) (2022-06-18)


### Bug Fixes

* Add missing legacy redirect registration route ([e1431f1](https://github.com/geokrety/geokrety-website/commit/e1431f171731135362411ff735ad5bb72c355930))
* Do not hide php errors on dev ([a50a801](https://github.com/geokrety/geokrety-website/commit/a50a801278d8757cef110250026bec4c754e9c34))
* xml export header already sent ([ec52f0b](https://github.com/geokrety/geokrety-website/commit/ec52f0b245550734874d9996c5a736c0f7ae9761))

## [2.22.1](https://github.com/geokrety/geokrety-website/compare/v2.22.0...v2.22.1) (2022-06-18)


### Bug Fixes

* Save sessiongid in audit logs ([1c27c54](https://github.com/geokrety/geokrety-website/commit/1c27c54eb69370e927e48ea7660897dce256a71a))

## [2.22.0](https://github.com/geokrety/geokrety-website/compare/v2.21.2...v2.22.0) (2022-06-18)


### Features

* Allow admin to reset rate-limits ([d89f47f](https://github.com/geokrety/geokrety-website/commit/d89f47ff1905aed1277f1c5a2fa4b011b6f0068e))
* Audit log move parameters ([9174008](https://github.com/geokrety/geokrety-website/commit/9174008e235cd106c6225e89d4d1b3ed1defb0a2))
* Set GeoKrety version in headers and rate limits ([94d2dfb](https://github.com/geokrety/geokrety-website/commit/94d2dfb3f2ae65f77a8c5c4f4e2aedd25db8529f))


### Bug Fixes

* Adapt tests to new messages ([12cb096](https://github.com/geokrety/geokrety-website/commit/12cb09649f09e62d805e4c11bdfe16d6a8f5e1e7))
* Add a dev option to force logs all posts values ([f667831](https://github.com/geokrety/geokrety-website/commit/f6678312ae6fee8cc0514f2ae41b6b0c89f03fda))
* Add email subject to audit logs ([a8b02b6](https://github.com/geokrety/geokrety-website/commit/a8b02b6923582695145f829f3662ea0e692ff50c))
* Add missing event listener ([c1aa17f](https://github.com/geokrety/geokrety-website/commit/c1aa17f0708ed7696918b8cf155448fb39bae977))
* ADd new schema in copy scripts ([880c08c](https://github.com/geokrety/geokrety-website/commit/880c08cdb2d1e83e417656125e0fd36090972e2d))
* Add rate-limit audi logs ([a1b3140](https://github.com/geokrety/geokrety-website/commit/a1b3140fd346d6d58cd7880dd468e7c0606178d0))
* Audit log xml errors objects ([e4a7f7c](https://github.com/geokrety/geokrety-website/commit/e4a7f7c6861489d2098c0f2e4349d16b8ec5d481))
* Close session after using secid ([b2081f7](https://github.com/geokrety/geokrety-website/commit/b2081f74d3fe2d438844f52df93982c3df8c818e))
* Fix refresh secid ([4981c08](https://github.com/geokrety/geokrety-website/commit/4981c08d5a29d6f20fd697f4d683027736ea72ff))
* Move audit tables to audit schema ([6dfbdeb](https://github.com/geokrety/geokrety-website/commit/6dfbdeb7a462f0906597d5dfbcae389164c8ddb0))
* nginx now returns 429 on rate limit excess ([abe26b6](https://github.com/geokrety/geokrety-website/commit/abe26b67578c8954b02e84fdf2bd54de825ac3b9))
* Prevent line wrap ([91e6c86](https://github.com/geokrety/geokrety-website/commit/91e6c86cf11ebf0246cc40762f6b660f6e00126b))

## [2.21.2](https://github.com/geokrety/geokrety-website/compare/v2.21.1...v2.21.2) (2022-06-12)


### Translations

* New Crowdin updates ([#725](https://github.com/geokrety/geokrety-website/issues/725)) ([6ba84f3](https://github.com/geokrety/geokrety-website/commit/6ba84f35e3e4e7e2a5ea702c1aa137037a6a4dea))

## [2.21.1](https://github.com/geokrety/geokrety-website/compare/v2.21.0...v2.21.1) (2022-06-12)


### Bug Fixes

* Prevent error in export details ([cb355a6](https://github.com/geokrety/geokrety-website/commit/cb355a66678d0a6041e0be99900a22596dc07a04))

## [2.21.0](https://github.com/geokrety/geokrety-website/compare/v2.20.2...v2.21.0) (2022-06-12)


### Features

* Add admin endpoint to see raw prometheus exporter ([d728d22](https://github.com/geokrety/geokrety-website/commit/d728d222bd5b8e7a5c9d241863b331e9da1b2817))
* Add export2 with details ([2872e36](https://github.com/geokrety/geokrety-website/commit/2872e3606dfc98bf04380ed7e28e45036e731e0d))
* Add other images to XML export2 details ([5c81ce0](https://github.com/geokrety/geokrety-website/commit/5c81ce0deb42c30e52edd5b906105524e7bc6704)), closes [#611](https://github.com/geokrety/geokrety-website/issues/611)
* Add rate limit per api call ([9833cc0](https://github.com/geokrety/geokrety-website/commit/9833cc04b87b5579a5248938740e142eaff85ff0))
* Add support for legacy GKM api endpoints ([8e388ae](https://github.com/geokrety/geokrety-website/commit/8e388ae7237206b291ff04d9f9637f6b83b79063))
* Allow GeoKrety list ordering ([8151953](https://github.com/geokrety/geokrety-website/commit/8151953a9bb2520d9ab659c487e0e116d9493e5d)), closes [#605](https://github.com/geokrety/geokrety-website/issues/605)
* Allow more datetime formats in xml exporters ([60b1752](https://github.com/geokrety/geokrety-website/commit/60b1752448322a0091e3ed21abe2305643977fb3))
* Allow Moves list ordering ([fed6ee2](https://github.com/geokrety/geokrety-website/commit/fed6ee2288f9bf968e80081e3a9b4bcf75c81ad8)), closes [#605](https://github.com/geokrety/geokrety-website/issues/605)


### Bug Fixes

* Add an export2 with details example ([209ddb0](https://github.com/geokrety/geokrety-website/commit/209ddb04d6812fb91aa5e931e1da4d4294318a07))
* Adjust rate limits ([e52c0c0](https://github.com/geokrety/geokrety-website/commit/e52c0c0c702a3a18736e98959a8eae183cae74bf))
* Allow showing 100 element in datatables ([faba4a9](https://github.com/geokrety/geokrety-website/commit/faba4a9a178227a2e29529f129d6a4ecfc3419f7))
* Don't refresh materialized concurrently on dev reset ([fbb8b51](https://github.com/geokrety/geokrety-website/commit/fbb8b5156dd0562040cda31d98f7664e510e7e03))
* Fix export* get inventory ([dfb3e03](https://github.com/geokrety/geokrety-website/commit/dfb3e03f4eece6cca62fd6be8f18a22508d89203))
* Fix htmlentities breaking change in php 8.1 ([8ef27ba](https://github.com/geokrety/geokrety-website/commit/8ef27bafee2336d6024a07068cce89831b015c04))
* Fix posting move on legacy endpoint ([b4166b3](https://github.com/geokrety/geokrety-website/commit/b4166b36908a723e436c084c409642eb7732a8d7))
* Fix some php 8.1 issues ([a50ce04](https://github.com/geokrety/geokrety-website/commit/a50ce04ee532fbb78d6747506c8704e6bae37eac))
* Leave an xml entry with the legacy typo ([37a14b5](https://github.com/geokrety/geokrety-website/commit/37a14b5b6f5f84a5b0b2c0df13c7d9dd8bbf23b4))
* Redefine rate limit for export2 endpoint ([74d071e](https://github.com/geokrety/geokrety-website/commit/74d071ea6917fd308be58f104460d1a4ab730d1f))
* Refresh materialized views during tests ([d71d864](https://github.com/geokrety/geokrety-website/commit/d71d864c22e918ed61cdf425a8b8d50d067100dd))
* Update some dependencies ([b070d55](https://github.com/geokrety/geokrety-website/commit/b070d55903503ec1250149f9b990836fdc54b4a6))
* Workaround bcosca/fatfree-core/issues[#345](https://github.com/geokrety/geokrety-website/issues/345) ([05ad4d9](https://github.com/geokrety/geokrety-website/commit/05ad4d9933a73821bfab046f8a6f3bce12d67355))


### Performance Improvements

* Greatly improve move save time ([891855a](https://github.com/geokrety/geokrety-website/commit/891855a6338fba8ac247c7cf9779fc897834e16a))


### Style

* Add some type hints ([40bce9d](https://github.com/geokrety/geokrety-website/commit/40bce9d0fca526100a10bde62a3284b908184496))
* Move api endpoints to dedicated file ([aa112bb](https://github.com/geokrety/geokrety-website/commit/aa112bb83927026434b9ceec25c62e85e6ebe96c))


### Code Refactoring

* Don't use ERROR from hive ([54862f7](https://github.com/geokrety/geokrety-website/commit/54862f793aa94bd460151c0f8582253774437b0d))
* Move code into dedicated function ([22718a4](https://github.com/geokrety/geokrety-website/commit/22718a4dd8a29801f28c66ef8e4cc16525b18137))


### Chores

* Disable download gk track ([ea16ff0](https://github.com/geokrety/geokrety-website/commit/ea16ff042345be231fb5becd54c1755ff12360c2))
* Reindent composer.json ([2de86ab](https://github.com/geokrety/geokrety-website/commit/2de86abc04ed188936a38216315c85d07f3c005e))
* Save some useful links ([bba087f](https://github.com/geokrety/geokrety-website/commit/bba087f95ff46bf6ee16d9df0f1afe108b5b2fa4))

### [2.20.2](https://github.com/geokrety/geokrety-website/compare/v2.20.1...v2.20.2) (2022-05-25)


### Translations

* New Crowdin updates ([#721](https://github.com/geokrety/geokrety-website/issues/721)) ([f11285d](https://github.com/geokrety/geokrety-website/commit/f11285db1395efff6cf6e2083e17a2f558725629))

### [2.20.1](https://github.com/geokrety/geokrety-website/compare/v2.20.0...v2.20.1) (2022-04-09)


### Bug Fixes

* Fix GeoKrety create CSRF ([f892114](https://github.com/geokrety/geokrety-website/commit/f8921147e4aa19c0cedc78b042e5a44165834ca1))

## [2.20.0](https://github.com/geokrety/geokrety-website/compare/v2.19.0...v2.20.0) (2022-04-09)


### Features

* Allow to choose default GeoKret template on creation ([0edc11f](https://github.com/geokrety/geokrety-website/commit/0edc11f57fd8696a659f35a26022744883eea7e7))


### Translations

* New translations ([d7b65d4](https://github.com/geokrety/geokrety-website/commit/d7b65d4683155a7fb020dac2c35d78915ffabe2c))

## [2.19.0](https://github.com/geokrety/geokrety-website/compare/v2.18.2...v2.19.0) (2022-04-09)


### Features

* Finish mass label generator ([8236a3a](https://github.com/geokrety/geokrety-website/commit/8236a3a1d100125ba08da23ed0ba03d9662e51bb)), closes [#581](https://github.com/geokrety/geokrety-website/issues/581)


### Bug Fixes

* persist sessions only when really authenticated ([9cabda1](https://github.com/geokrety/geokrety-website/commit/9cabda139aa29deb9a7a70fb7143c11524a1f191))


### Style

* Fix typo ([653722b](https://github.com/geokrety/geokrety-website/commit/653722b53e2d6bfb9b384866ac535cf1eeb7c20d))


### Translations

* New translations ([2b86ff3](https://github.com/geokrety/geokrety-website/commit/2b86ff34df131e92d7e48ae0871534863d535f22))

### [2.18.2](https://github.com/geokrety/geokrety-website/compare/v2.18.1...v2.18.2) (2022-04-03)


### Bug Fixes

* Reformat delete user modal content ([cb9a1d5](https://github.com/geokrety/geokrety-website/commit/cb9a1d5392214c249193f2a8d370ed0355e747d8))

### [2.18.1](https://github.com/geokrety/geokrety-website/compare/v2.18.0...v2.18.1) (2022-04-03)


### Bug Fixes

* Activate recaptcha properly on user delete modal ([60e22fa](https://github.com/geokrety/geokrety-website/commit/60e22fa924b00506472ef252882ecd0e24a3b62c))

## [2.18.0](https://github.com/geokrety/geokrety-website/compare/v2.17.3...v2.18.0) (2022-04-02)


### Features

* Add link to releases changelogs ([df21b77](https://github.com/geokrety/geokrety-website/commit/df21b771eb3759177734fde8a5be37e616483cad))
* Allow users to deleted their account ([811d715](https://github.com/geokrety/geokrety-website/commit/811d715fbe7c40d3addfe32fa54a8cf31b74e2f9))


### Bug Fixes

* Disable map mouse scroll except for Geokrety map ([9aab37e](https://github.com/geokrety/geokrety-website/commit/9aab37e71aaabde7539847f8d7b677985e91d246))


### Chores

* Clean comment ([2dd1e44](https://github.com/geokrety/geokrety-website/commit/2dd1e4455e26e838e37dc62ad5e311d5b2e96d8d))

### [2.17.3](https://github.com/geokrety/geokrety-website/compare/v2.17.2...v2.17.3) (2022-03-17)


### Translations

* New Crowdin updates ([#717](https://github.com/geokrety/geokrety-website/issues/717)) ([4f1c330](https://github.com/geokrety/geokrety-website/commit/4f1c3300ed51cf1e41ac61b838c80e1e3623ff15))

### [2.17.2](https://github.com/geokrety/geokrety-website/compare/v2.17.1...v2.17.2) (2022-03-17)


### Bug Fixes

* Fix small bug introduced by ee6bf1b6 ([6d1d61c](https://github.com/geokrety/geokrety-website/commit/6d1d61c055a9e4c93eceab0716f8ddcd0ba4ade9))

### [2.17.1](https://github.com/geokrety/geokrety-website/compare/v2.17.0...v2.17.1) (2022-03-17)


### Bug Fixes

* Fix create account via OAuth2 ([ee6bf1b](https://github.com/geokrety/geokrety-website/commit/ee6bf1b6b9a6abec4e218e9f77840e9e9f91da6c))
* Fix the import script in some places ([c96bd93](https://github.com/geokrety/geokrety-website/commit/c96bd93b4090ef0b61737c2635f4f830465910e3))

## [2.17.0](https://github.com/geokrety/geokrety-website/compare/v2.16.2...v2.17.0) (2022-03-10)


### Features

* Add request limits on endpoints ([fae4f3a](https://github.com/geokrety/geokrety-website/commit/fae4f3ad3a1b306d883ef9174f4586df098de116)), closes [#12](https://github.com/geokrety/geokrety-website/issues/12) [#421](https://github.com/geokrety/geokrety-website/issues/421)


### Bug Fixes

* Another database-migrator fix ([00d38d2](https://github.com/geokrety/geokrety-website/commit/00d38d2819a6978193a4848272d5388d14feb414))
* Try to fix map autobound one more time ([90f951b](https://github.com/geokrety/geokrety-website/commit/90f951b1d969b8ca6cbddcf3aafc451fd02e27f4))


### Performance Improvements

* Enhance a geojson database query ([6096976](https://github.com/geokrety/geokrety-website/commit/6096976bb8fdb8982d70d584222c2b9141235ce6))


### Code Refactoring

* Symplify a test condition ([fb10d89](https://github.com/geokrety/geokrety-website/commit/fb10d89f7393c6d0b959e024b19b51ce3df8ae60))


### Style

* Migrate go2geo to bootstrap ([bf2c221](https://github.com/geokrety/geokrety-website/commit/bf2c221dd0ce06e33d1089373972f53c5c1b2716))

### [2.16.2](https://github.com/geokrety/geokrety-website/compare/v2.16.1...v2.16.2) (2022-03-06)


### Bug Fixes

* Create missing index on gk_geokrety_in_caches view ([9e9d4fb](https://github.com/geokrety/geokrety-website/commit/9e9d4fb81bc793b55040b58a8bc8a7a45961a964))
* Fix database-migrator timeout on gk-moves postProcess ([438174c](https://github.com/geokrety/geokrety-website/commit/438174c9fec407e707733150c4d722f80e8c599a))
* remove host from nginx logs ([7237fbc](https://github.com/geokrety/geokrety-website/commit/7237fbc42ac862d0a32650c2d2a97585466e9e23))
* Speedup Geokrety Map geojson ([c4229b6](https://github.com/geokrety/geokrety-website/commit/c4229b61b1c52ff0a5259b1a78baf3be60acc8ad))

### [2.16.1](https://github.com/geokrety/geokrety-website/compare/v2.16.0...v2.16.1) (2022-02-26)


### Bug Fixes

* Fix docker-compose configuration ([95bd135](https://github.com/geokrety/geokrety-website/commit/95bd135c42bb5fe0657a3ea7dbbc40f843bea71e))
* Nginx has only 2 php files allowed ([d96fcd1](https://github.com/geokrety/geokrety-website/commit/d96fcd1333c0c4be3b47804c46895b0d297de656))

## [2.16.0](https://github.com/geokrety/geokrety-website/compare/v2.15.2...v2.16.0) (2022-02-26)


### Features

* Initial migration to php-fpm ([8783a12](https://github.com/geokrety/geokrety-website/commit/8783a12ef614123a3ce04b59660c7881675c74bd))

### [2.15.2](https://github.com/geokrety/geokrety-website/compare/v2.15.1...v2.15.2) (2022-02-26)


### Bug Fixes

* Fix cron environment variables ([3a2c367](https://github.com/geokrety/geokrety-website/commit/3a2c367bccb6e7502123d276545f62eba260438d))

### [2.15.1](https://github.com/geokrety/geokrety-website/compare/v2.15.0...v2.15.1) (2022-02-25)


### Translations

* New Crowdin updates ([#711](https://github.com/geokrety/geokrety-website/issues/711)) ([3dda143](https://github.com/geokrety/geokrety-website/commit/3dda143e3e9679dc6a0b5f31b6876e3294d23fef))

## [2.15.0](https://github.com/geokrety/geokrety-website/compare/v2.14.6...v2.15.0) (2022-02-25)


### Features

* Find libravatar url asynchronously ([a3dc1a9](https://github.com/geokrety/geokrety-website/commit/a3dc1a9f4f35c239d91d71ef401355ecb88e1f66))


### Bug Fixes

* Create missing index on moves table ([efb6e73](https://github.com/geokrety/geokrety-website/commit/efb6e7376bd07ac201d8e6ae78443aa70455fda1))
* Fix centering map on Where are my Geokrety page ([ea986d8](https://github.com/geokrety/geokrety-website/commit/ea986d8d7f6ac350528d8b7fdb8fc0e72a1d5912))
* Fix display owner in Owned Geokrety Map popups ([25da79a](https://github.com/geokrety/geokrety-website/commit/25da79a0003b6ad563873eb2b00a2f09c0fa3e2d))
* Fix string translation ([1e1b031](https://github.com/geokrety/geokrety-website/commit/1e1b0312dba276c46557030468c470d0598e3db1))
* Use smarty fetch instead of display to return string ([e73d5aa](https://github.com/geokrety/geokrety-website/commit/e73d5aa8e3c5cbbccdfaaf3962a457e56622f490))


### Style

* Define function return types ([a81c926](https://github.com/geokrety/geokrety-website/commit/a81c92692406e7f1ab866880d6268377ac7ce4f5))
* Remove unnecessary null check ([4e96b13](https://github.com/geokrety/geokrety-website/commit/4e96b135e1fd9763629da14491d04c19e99c1994))
* Use sprintf instead of concatenation ([4c20ec8](https://github.com/geokrety/geokrety-website/commit/4c20ec8f8c0ec33dabd3d01fb9ef8ac7b6e62936))


### Chores

* Correct docstrings format ([9fc5bf0](https://github.com/geokrety/geokrety-website/commit/9fc5bf0279deb0078c82df7ed4a17b323c285eef))
* Format fix from phpcs ([f0285b2](https://github.com/geokrety/geokrety-website/commit/f0285b22056f5952703b91129e3937739ab7e999))

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
