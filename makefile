CONTAINER_NAME=golem-webui
PORT=4200

.PHONY: default run clean stop
default:
	sudo docker build -t $(CONTAINER_NAME) ./webserver

appkey.txt:
	@if ! [ -f  appkey.txt ]; then \
		if ! [ -z "$$YAGNA_APPKEY" ]; then \
			echo $$YAGNA_APPKEY > appkey.txt; \
		else \
			echo "please put you YAGNA APPKEY in appkey.txt or in env var YAGNA_APPKEY"; \
		fi; \
	fi

webserver/backend/requestor.py: requestor.py
	ln requestor.py webserver/backend/requestor.py

run: appkey.txt default webserver/backend/requestor.py
	sudo docker run -dit --name $(CONTAINER_NAME) \
	 	-p $(PORT):80 \
		-v $$(pwd)/webserver/public_html:/var/www/html \
		-v $$(pwd)/webserver/backend:/backend \
		$(CONTAINER_NAME)
	sudo docker exec -ti $(CONTAINER_NAME) "chgrp" "-R" "www-data" "/backend"
	sudo docker exec -ti $(CONTAINER_NAME) "yagna" "service" "run"
	sudo docker exec -ti $(CONTAINER_NAME) "export" "YAGNA_APPKEY=$$(cat appkey.txt)"

exec: 
	sudo docker exec -ti $(CONTAINER_NAME) "/bin/bash"

stop:
	sudo docker stop $(CONTAINER_NAME)

clean: stop
	sudo docker rm $(CONTAINER_NAME)

