SHELL := /bin/bash
CURRENT_DIR:=$(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
PTY_PREFIX=
ifeq (Windows_NT, ${OS})
	PTY_PREFIX=winpty
endif

TRAVIS_BRANCH ?= local

# HELP
# This will output the help for each task
# thanks to https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
.PHONY: help

help: ## This help.
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z0-9_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

test: ## run robot framework tests (master env)
	@echo "Run Tests"
	time PATH=${PATH}:${PWD} robot --variable browser:Firefox -v images_dir:visual_images --output output.xml --debugfile debugfile.log --log log.html --report report.html --xunit xUnit.xml -d docs/$(TRAVIS_BRANCH) -V acceptance/vars/robot-vars.py -e TODO acceptance/
	sed -i 's@\(<td><a href="${$item.logURL}">${$item.logURL}\)</a></td>@\1<tr><th>Visual Report:</th><td><a href="visualReport.html">visualReport.html</a></td></tr>@' docs/$(TRAVIS_BRANCH)/report.html

local:  ## run Robot Framework remotely on BrowserStack
	BS_ENABLED=1 BS_LOCAL=true BS_LOCAL_ID=${USER}-local make test

bs-agent:  ## Launch BrowserStack Local agent
	BrowserStackLocal --key ${BS_TOKEN} --force-local --local-identifier "${USER}-local"

install_robot-framework: ## Install robot framework
	pip install -r requirements.txt

download_geckodriver:  ## Download geckodriver
	curl -L https://github.com/mozilla/geckodriver/releases/download/v0.27.0/geckodriver-v0.27.0-linux64.tar.gz | tar xf -

download_bslocal:  ## Download BrowserStack Local agent
	curl -L https://www.browserstack.com/browserstack-local/BrowserStackLocal-linux-x64.zip | busybox unzip -
