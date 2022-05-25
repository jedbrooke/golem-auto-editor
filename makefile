CONTAINER_NAME=golem-webui
PORT=4200

.PHONY: default run clean stop
default:
	sudo docker build -t $(CONTAINER_NAME) ./webserver

webserver/backend/appkey.txt:
	@if ! [ -f webserver/backend/appkey.txt ]; then \
		if ! [ -z "$$YAGNA_APPKEY" ]; then \
			echo $$YAGNA_APPKEY > webserver/backend/appkey.txt; \
		else \
			echo "please put you YAGNA APPKEY in webserver/backend/appkey.txt or in env var YAGNA_APPKEY"; \
		fi; \
	fi

webserver/backend/requestor.py: requestor.py
	@if ! [ -f webserver/backend/requestor.py ]; then \
		ln requestor.py webserver/backend/requestor.py; \
	fi

webserver/backend/pepper.txt:
	cat /dev/random | tr -dc "[:alnum:]" | head -c 32 > webserver/backend/pepper.txt

run: webserver/backend/appkey.txt default webserver/backend/requestor.py
	sudo docker run -dit --name $(CONTAINER_NAME) \
	 	-p $(PORT):80 \
		-v $$(pwd)/webserver/public_html:/var/www/html \
		-v $$(pwd)/webserver/backend:/var/www/backend \
		$(CONTAINER_NAME)
	sudo docker exec -tid $(CONTAINER_NAME) "yagna" "service" "run"
	@echo "initializing yagna"
	@sleep 5
	sudo docker exec -ti $(CONTAINER_NAME) "yagna" "app-key" "create" "requestor" > webserver/backend/appkey.txt
	sudo docker exec -ti $(CONTAINER_NAME) "chgrp" "-R" "www-data" "/var/www/backend"
	sudo docker exec -ti $(CONTAINER_NAME) "yagna" "payment" "fund"
	sudo docker exec -ti $(CONTAINER_NAME) "yagna" "payment" "status"

start:
	sudo docker start $(CONTAINER_NAME)
	sudo docker exec -tid $(CONTAINER_NAME) "yagna" "service" "run"
	sudo docker exec -ti $(CONTAINER_NAME) "yagna" "payment" "init" "--sender"
	sudo docker exec -ti $(CONTAINER_NAME) "python3" "/var/www/backend/jobserver.py"

exec: 
	sudo docker exec -ti $(CONTAINER_NAME) "/bin/bash"

stop:
	sudo docker stop $(CONTAINER_NAME)

clean: stop
	sudo docker rm $(CONTAINER_NAME)

.PHONY: clean_jobs
clean_jobs:
	rm -f webserver/backend/queue/files/*
	rm -f webserver/backend/queue/jobs/*
	rm -f webserver/backend/finished/*


