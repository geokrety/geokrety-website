SHELL := /bin/bash
CURRENT_DIR:=$(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
PTY_PREFIX=
ifeq (Windows_NT, ${OS})
	PTY_PREFIX=winpty
endif

GIT_BRANCH ?= local
ROBOT_PARAMS := --debugfile debugfile.log --report report.html -d docs/$(GIT_BRANCH) -V acceptance/ressources/robot-vars.py acceptance

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

test: ## run robot framework tests (master env)
	@echo "Run Tests"
	time PATH=${PATH}:${PWD} robot --log log.html --output output.xml $(ROBOT_PARAMS)
	sed -i 's@\(<td><a href="${$item.logURL}">${$item.logURL}\)</a></td>@\1<tr><th>Visual Report:</th><td><a href="visualReport.html">visualReport.html</a></td></tr>@' docs/$(GIT_BRANCH)/report.html

rerun-failed-tests: ## rerun robot framework failed tests
	@echo "ReRun Failing Tests"
	time PATH=${PATH}:${PWD} robot --log log-rerun.html --output rerun.xml --rerunfailed docs/$(GIT_BRANCH)/output.xml $(ROBOT_PARAMS)
	sed -i 's@\(<td><a href="${$item.logURL}">${$item.logURL}\)</a></td>@\1<tr><th>Visual Report:</th><td><a href="visualReport.html">visualReport.html</a></td></tr>@' docs/$(GIT_BRANCH)/report.html

	@echo "Merging reports"
	rebot --nostatusrc --outputdir docs/$(GIT_BRANCH) --output output.xml --merge docs/$(GIT_BRANCH)/output.xml  docs/$(GIT_BRANCH)/rerun.xml


local:  ## run Robot Framework remotely on BrowserStack
	BS_ENABLED=1 BS_LOCAL=true BS_LOCAL_ID=${USER}-local make test

bs-agent:  ## Launch BrowserStack Local agent
	BrowserStackLocal --key ${BS_TOKEN} --force-local --local-identifier "${USER}-local"

install_robot-framework: ## Install robot framework
	pip install -r requirements.txt

download_geckodriver:  ## Download geckodriver
	curl -L https://github.com/mozilla/geckodriver/releases/download/v0.33.0/geckodriver-v0.33.0-linux64.tar.gz | tar xzf -

download_bslocal:  ## Download BrowserStack Local agent
	curl -L https://www.browserstack.com/browserstack-local/BrowserStackLocal-linux-x64.zip | busybox unzip -
