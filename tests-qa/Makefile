SHELL := /bin/bash
CURRENT_DIR:=$(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
GK_INSTANCE_COUNT ?= 1
SHARD_ID ?= 1
SHARD_COUNT ?= 10
PTY_PREFIX=
ifeq (Windows_NT, ${OS})
	PTY_PREFIX=winpty
endif

GIT_BRANCH ?= local
ROBOT_PARAMS := -d docs/$(GIT_BRANCH) -V acceptance/ressources/vars/robot-vars.py acceptance
# ROBOT_PARAMS := -t 'Information Should Be Loaded' --debugfile debugfile.log --report report.html -d docs/$(GIT_BRANCH) -V acceptance/ressources/robot-vars.py acceptance
# ROBOT_PARAMS := -s "170 Xml Export Oc" --debugfile debugfile.log --report report.html -d docs/$(GIT_BRANCH) -V acceptance/ressources/robot-vars.py acceptance
# ROBOT_PARAMS := -s "170 Xml Export Oc" -t 'ModifiedSince Is Limited To Last 10 Days' --debugfile debugfile.log --report report.html -d docs/$(GIT_BRANCH) -V acceptance/ressources/robot-vars.py acceptance

# HELP
# This will output the help for each task
# thanks to https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
.PHONY: help

help: ## This help.
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z0-9_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

echo-run-command:
	@echo robot --log log.html --output output.xml $(ROBOT_PARAMS)

test-headless: ## Run tests headless mode
	@echo "Run tests headless"
	HEADLESS=True make test

rerun-failed-tests-headless: ## rerun robot framework failed tests headless mode
	@echo "Run rerun-failed-tests headless"
	HEADLESS=True make rerun-failed-tests

test-robot: ## run robot framework tests
	@echo "Run Tests"
	time PATH=${PATH}:${PWD} robot --log log.html --output output.xml $(ROBOT_PARAMS)
	@sed -i 's@\(<td><a href="${$item.logURL}">${$item.logURL}\)</a></td>@\1<tr><th>Visual Report:</th><td><a href="visualReport.html">visualReport.html</a></td></tr>@' docs/$(GIT_BRANCH)/report.html


test-pabot-shard: ## run robot framework tests
	@echo "Run Tests: shard $(SHARD_ID)/$(SHARD_COUNT)"
	time PATH=${PATH}:${PWD} pabot \
		--shard $(SHARD_ID)/$(SHARD_COUNT) \
		--chunk \
		--processes $(GK_INSTANCE_COUNT) \
		--output output-$(SHARD_ID).xml \
		--log log-$(SHARD_ID).html \
		--report report-$(SHARD_ID).html \
		$(ROBOT_PARAMS)

test-pabot: ## run robot framework tests
	@echo "Run Tests"
	time PATH=${PATH}:${PWD} pabot \
		--chunk \
		--processes $(GK_INSTANCE_COUNT) \
		--output output.xml \
		$(ROBOT_PARAMS)

test: test-pabot ## run robot framework tests


rerun-failed-tests-shard: ## rerun robot framework failed tests
	@echo "ReRun Failing Tests"
	time PATH=${PATH}:${PWD} pabot \
		--chunk \
		--processes $(GK_INSTANCE_COUNT) \
		--output output-rerun-$(SHARD_ID).xml \
		--log log-rerun-$(SHARD_ID).html \
		--report report-$(SHARD_ID).html \
		--rerunfailed docs/$(GIT_BRANCH)/output-$(SHARD_ID).xml \
		$(ROBOT_PARAMS)

rerun-failed-tests: ## rerun robot framework failed tests
	@echo "ReRun Failing Tests"
	time PATH=${PATH}:${PWD} pabot \
		--chunk \
		--processes $(GK_INSTANCE_COUNT) \
		--log log-rerun.html \
		--output rerun.xml \
		--rerunfailed docs/$(GIT_BRANCH)/output.xml \
		$(ROBOT_PARAMS)
	# @sed -i 's@\(<td><a href="${$item.logURL}">${$item.logURL}\)</a></td>@\1<tr><th>Visual Report:</th><td><a href="visualReport.html">visualReport.html</a></td></tr>@' docs/$(GIT_BRANCH)/report.html

	@echo "Merging reports"
	rebot --nostatusrc --outputdir docs/$(GIT_BRANCH) --output output.xml --merge docs/$(GIT_BRANCH)/output.xml  docs/$(GIT_BRANCH)/rerun.xml


local:  ## run Robot Framework remotely on BrowserStack
	BS_ENABLED=1 BS_LOCAL=true BS_LOCAL_ID=${USER}-local make test

launch-bs-agent:  ## Launch BrowserStack Local agent
	[ -n "${BS_TOKEN}" ] || { echo "BS_TOKEN env is missing"; exit 1; }
	./BrowserStackLocal --key ${BS_TOKEN} --force-local --local-identifier "${USER}-local"

launch-selenoid: ## Launch Selenoid agent
	./selenoid_linux_amd64 -capture-driver-logs -conf selenoid-browsers.json

pull-selenoid-images: ## Pull Selenoid images
	while read i; do docker pull "$$i"; done < <(cat selenoid-browsers.json |jq -r '. | to_entries[] | .value | .versions | to_entries[] | .value | .image')
	docker pull selenoid/video-recorder:latest-release

install_robot-framework: ## Install robot framework
	pip install -r requirements.txt

download_geckodriver:  ## Download geckodriver
	curl -L https://github.com/mozilla/geckodriver/releases/download/v0.35.0/geckodriver-v0.35.0-linux64.tar.gz | tar xzf -

download_selenoid:  ## Download selenoid
	wget https://github.com/aerokube/selenoid/releases/download/1.11.3/selenoid_linux_amd64
	chmod +x selenoid_linux_amd64

download_bslocal:  ## Download BrowserStack Local agent
	curl -L https://www.browserstack.com/browserstack-local/BrowserStackLocal-linux-x64.zip | busybox unzip -
	chmod +x BrowserStackLocal
