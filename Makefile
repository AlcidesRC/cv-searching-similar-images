.DEFAULT_GOAL := help

###
# CONSTANTS
###

ifneq (,$(findstring xterm,$(TERM)))
	BLACK   := $(shell tput -Txterm setaf 0)
	RED     := $(shell tput -Txterm setaf 1)
	GREEN   := $(shell tput -Txterm setaf 2)
	YELLOW  := $(shell tput -Txterm setaf 3)
	BLUE    := $(shell tput -Txterm setaf 4)
	MAGENTA := $(shell tput -Txterm setaf 5)
	CYAN    := $(shell tput -Txterm setaf 6)
	WHITE   := $(shell tput -Txterm setaf 7)
	RESET   := $(shell tput -Txterm sgr0)
else
	BLACK   := ""
	RED     := ""
	GREEN   := ""
	YELLOW  := ""
	BLUE    := ""
	MAGENTA := ""
	CYAN    := ""
	WHITE   := ""
	RESET   := ""
endif

#---

SERVICE_CADDY = caddy
SERVICE_APP   = app1

#---

WEBSITE_URL = https://localhost

#---

HOST_USER_ID    := $(shell id --user)
HOST_USER_NAME  := $(shell id --user --name)
HOST_GROUP_ID   := $(shell id --group)
HOST_GROUP_NAME := $(shell id --group --name)

#---

DOCKER_COMPOSE         = docker compose --file docker/docker-compose.yml --file docker/docker-compose.override.$(env).yml

DOCKER_BUILD_ARGUMENTS = --build-arg="HOST_USER_ID=$(HOST_USER_ID)" --build-arg="HOST_USER_NAME=$(HOST_USER_NAME)" --build-arg="HOST_GROUP_ID=$(HOST_GROUP_ID)" --build-arg="HOST_GROUP_NAME=$(HOST_GROUP_NAME)"

DOCKER_RUN             = $(DOCKER_COMPOSE) run --rm $(SERVICE_APP)
DOCKER_RUN_AS_USER     = $(DOCKER_COMPOSE) run --rm --user $(HOST_USER_ID):$(HOST_GROUP_ID) $(SERVICE_APP)

###
# FUNCTIONS
###

require-%:
	@if [ -z "$($(*))" ] ; then \
		echo "" ; \
		echo " ${RED}⨉${RESET} Parameter [ ${YELLOW}${*}${RESET} ] is required!" ; \
		echo "" ; \
		echo " ${YELLOW}ℹ${RESET} Usage [ ${YELLOW}make <command>${RESET} ${RED}${*}=${RESET}${YELLOW}xxxxxx${RESET} ]" ; \
		echo "" ; \
		exit 1 ; \
	fi;

define taskDone
	@echo ""
	@echo " ${GREEN}✓${RESET}  ${GREEN}Task done!${RESET}"
	@echo ""
endef

# $(1)=TEXT $(2)=EXTRA
define showInfo
	@echo " ${YELLOW}ℹ${RESET}  $(1) $(2)"
endef

# $(1)=TEXT $(2)=EXTRA
define showAlert
	@echo " ${RED}!${RESET}  $(1) $(2)"
endef

# $(1)=NUMBER $(2)=TEXT
define orderedList
	@echo ""
	@echo " ${CYAN}$(1).${RESET}  ${CYAN}$(2)${RESET}"
	@echo ""
endef

define pad
	$(shell printf "%-$(1)s" " ")
endef

###
# HELP
###

.PHONY: help
help:
	@clear
	@echo "${BLACK}"
	@echo "╔════════════════════════════════════════════════════════════════════════════════════════════════════════╗"
	@echo "║ $(call pad,96) ║"
	@echo "║ $(call pad,32) ${YELLOW}.:${RESET} AVAILABLE COMMANDS ${YELLOW}:.${BLACK} $(call pad,32) ║"
	@echo "║ $(call pad,96) ║"
	@echo "╚════════════════════════════════════════════════════════════════════════════════════════════════════════╝"
	@echo "${BLACK}·${RESET} ${MAGENTA}DOMAIN(s)${BLACK} .... ${CYAN}$(WEBSITE_URL)${BLACK}"
	@echo "${BLACK}·${RESET} ${MAGENTA}SERVICE(s)${BLACK} ... ${CYAN}$(shell docker ps --format "{{.Names}}")${BLACK}"
	@echo "${BLACK}·${RESET} ${MAGENTA}USER${BLACK} ......... ${WHITE}(${CYAN}$(HOST_USER_ID)${WHITE})${BLACK} ${CYAN}$(HOST_USER_NAME)${BLACK}"
	@echo "${BLACK}·${RESET} ${MAGENTA}GROUP${BLACK} ........ ${WHITE}(${CYAN}$(HOST_GROUP_ID)${WHITE})${BLACK} ${CYAN}$(HOST_GROUP_NAME)${BLACK}"
	@echo "${RESET}"
	@grep -E '^[a-zA-Z_0-9%-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "${BLACK}·${RESET} ${YELLOW}%-35s${RESET} %s\n", $$1, $$2}'
	@echo ""

###
# DOCKER RELATED
###

.PHONY: build
build: ## Docker: builds the service <env=[dev|prod]>
	@$(eval env ?= 'dev')
	$(call showInfo,"Building Docker image\(s\)...")
	@echo ""
	@COMPOSE_BAKE=true $(DOCKER_COMPOSE) build $(DOCKER_BUILD_ARGUMENTS)
	$(call taskDone)

.PHONY: up
up: ## Docker: starts the service <env=[dev|prod]>
	@$(eval env ?= 'dev')
	$(call showInfo,"Starting service\(s\)...")
	@echo ""
	@$(DOCKER_COMPOSE) up --remove-orphans --detach
	$(call taskDone)

.PHONY: restart
restart: ## Docker: restarts the service <env=[dev|prod]>
	@$(eval env ?= 'dev')
	$(call showInfo,"Restarting service\(s\)...")
	@echo ""
	@$(DOCKER_COMPOSE) restart
	$(call taskDone)

.PHONY: down
down: ## Docker: stops the service <env=[dev|prod]>
	@$(eval env ?= 'dev')
	$(call showInfo,"Stopping service\(s\)...")
	@echo ""
	@$(DOCKER_COMPOSE) down $(DOCKER_COMPOSE_FILES) --remove-orphans
	$(call taskDone)

.PHONY: logs
logs: ## Docker: exposes the service logs <env=[dev|prod]> <service=[app1|caddy]>
	@$(eval env ?= 'dev')
	@$(eval service ?= $(SERVICE_APP))
	$(call showInfo,"Exposing service\(s\) logs for [ $(service) ] service...")
	@echo ""
	@$(DOCKER_COMPOSE) logs -f $(service)
	$(call taskDone)

.PHONY: shell
shell: ## Docker: establish a shell session into main container
	@$(eval env ?= 'dev')
	$(call showInfo,"Establishing a shell terminal with main service...")
	@echo ""
	@$(DOCKER_RUN_AS_USER) sh
	$(call taskDone)

.PHONY: inspect
inspect: ## Docker: inspect the health for specific service <service=[app1|caddy]>
	@$(eval service ?= $(SERVICE_APP))
	$(call showInfo,"Inspecting the health for [ $(service) ] service...")
	@echo ""
	@docker inspect --format "{{json .State.Health}}" $(service) | jq
	@echo ""
	$(call taskDone)

###
# CADDY
###

.PHONY: install-caddy-certificate
install-caddy-certificate: up ## Setup: extracts the Caddy Local Authority certificate
	$(call showInfo,"Extracting Caddy Certificate Authority file...")
	@echo ""
	@echo "How to install [ $(YELLOW)Caddy Local Authority - 20XX ECC Root$(RESET) ] as a valid Certificate Authority"
	$(call orderedList,1,"Copy the root certificate from Caddy Docker container")
	@docker cp $(SERVICE_CADDY):/data/caddy/pki/authorities/local/root.crt ./caddy-root-ca-authority.crt
	$(call orderedList,2,"Install the Caddy Authority certificate into your browser")
	@echo "$(YELLOW)Chrome-based browsers (Chrome, Brave, etc)$(RESET)"
	@echo "- Go to [ Settings / Privacy & Security / Security / Manage Certificates / Authorities ]"
	@echo "- Import [ ./caddy-root-ca-authority.crt ]"
	@echo "- Check on [ Trust this certificate for identifying websites ]"
	@echo "- Save changes"
	@echo ""
	@echo "$(YELLOW)Firefox browser$(RESET)"
	@echo "- Go to [ Settings / Privacy & Security / Security / Certificates / View Certificates / Authorities ]"
	@echo "- Import [ ./caddy-root-ca-authority.crt ]"
	@echo "- Check on [ This certificate can identify websites ]"
	@echo "- Save changes"
	@echo ""
	$(call showInfo,"For further information, please visit https://caddyserver.com/docs/running#docker-compose")
	$(call taskDone)

###
# COMPOSER RELATED
###

.PHONY: composer-dump
composer-dump: ## Composer: runs <composer dump-auto>
	@$(eval env ?= 'dev')
	$(call showInfo,"Executing \<composer dump-auto\>")
	@echo ""
	@$(DOCKER_RUN_AS_USER) composer dump-auto --optimize $(COMPOSER_SHARED_OPTIONS)
	$(call taskDone)

.PHONY: composer-install
composer-install: ## Composer: runs <composer install>
	@$(eval env ?= 'dev')
	$(call showInfo,"Executing \<composer install\>")
	@echo ""
	@$(DOCKER_RUN_AS_USER) composer install --optimize-autoloader $(COMPOSER_SHARED_OPTIONS)
	$(call taskDone)

.PHONY: composer-remove
composer-remove: ## Composer: runs <composer remove>
	@$(eval env ?= 'dev')
	$(call showInfo,"Executing \<composer remove\>")
	@echo ""
	@$(DOCKER_RUN_AS_USER) composer remove $(COMPOSER_OPTIMIZE_OPTIONS) $(COMPOSER_SHARED_OPTIONS)
	$(call taskDone)

.PHONY: composer-require-dev
composer-require-dev: ## Composer: runs <composer require --dev>
	@$(eval env ?= 'dev')
	$(call showInfo,"Executing \<composer require --dev\>")
	@$(DOCKER_RUN_AS_USER) composer require $(COMPOSER_OPTIMIZE_OPTIONS) --dev $(COMPOSER_SHARED_OPTIONS)
	$(call taskDone)

.PHONY: composer-require
composer-require: ## Composer: runs <composer require>
	@$(eval env ?= 'dev')
	$(call showInfo,"Executing \<composer require\>")
	@echo ""
	@$(DOCKER_RUN_AS_USER) composer require $(COMPOSER_OPTIMIZE_OPTIONS) $(COMPOSER_SHARED_OPTIONS)
	$(call taskDone)

.PHONY: composer-update
composer-update: ## Composer: runs <composer update>
	@$(eval env ?= 'dev')
	$(call showInfo,"Executing \<composer update\>")
	@echo ""
	@$(DOCKER_RUN_AS_USER) composer update $(COMPOSER_OPTIMIZE_OPTIONS) $(COMPOSER_SHARED_OPTIONS)
	$(call taskDone)

###
# QA
###

.PHONY: check-syntax
check-syntax: ## Application: runs PHP linter
	@$(eval env ?= 'dev')
	$(call showInfo,"Checking PHP syntax...")
	@echo ""
	@$(DOCKER_RUN_AS_USER) composer check-style
	$(call taskDone)

.PHONY: check-style
check-style: ## Application: runs PHP Code Sniffer
	@$(eval env ?= 'dev')
	$(call showInfo,"Checking PHP style...")
	@echo ""
	@$(DOCKER_RUN_AS_USER) composer check-style
	$(call taskDone)

.PHONY: fix-style
fix-style: ## Application: runs PHP Code Beautifier and Fixer
	@$(eval env ?= 'dev')
	$(call showInfo,"Fixing PHP style...")
	@echo ""
	@$(DOCKER_RUN_AS_USER) composer check-style
	$(call taskDone)

.PHONY: phpstan
phpstan: ## Application: runs PHPStan
	@$(eval env ?= 'dev')
	$(call showInfo,"Executing PHPStan...")
	@echo ""
	@$(DOCKER_RUN_AS_USER) composer phpstan
	$(call taskDone)

.PHONY: phpmd
phpmd: ## Application: runs PHP Mess Detector
	@$(eval env ?= 'dev')
	$(call showInfo,"Executing PHP Mess Detector...")
	@echo ""
	@$(DOCKER_RUN_AS_USER) composer phpmd
	$(call taskDone)

.PHONY: tests
tests: ## Application: runs PHPUnit testsuite
	@$(eval env ?= 'dev')
	$(call showInfo,"Executing PHPUnit...")
	@echo ""
	@$(DOCKER_RUN_AS_USER) composer tests
	$(call taskDone)

###
# MISCELANEOUS
###

.PHONY: open-website
open-website: ## Application: open the application website
	$(call showInfo,"Opening web application...")
	@echo ""
	@xdg-open $(WEBSITE_URL)
	@$(call showAlert,"Press Ctrl+C to resume your session")
	$(call taskDone)

.PHONY: init
init: build composer-install install-caddy-certificate ## Application: initializes the application
	$(call showInfo,"When ready just execute [ make open-website ] to visit the website with your preferred browser")
	$(call taskDone)
