CONTAINER_NAME=golem-webui
PORT=4200

.PHONY: default run clean stop
default:
	sudo docker build -t $(CONTAINER_NAME) ./webserver

run:
	sudo docker run -dit --name $(CONTAINER_NAME) \
	 	-p $(PORT):80 \
		-v $$(pwd)/webserver/public_html:/var/www/html \
		-v $$(pwd)/webserver/backend:/backend \
		$(CONTAINER_NAME)
	sudo docker exec -ti $(CONTAINER_NAME) "chgrp" "-R" "www-data" "/backend"

stop:
	sudo docker stop $(CONTAINER_NAME)

clean: stop
	sudo docker rm $(CONTAINER_NAME)


