CONTAINER_NAME=golem-webui
PORT=4200

.PHONY: default run clean
default:
	sudo docker build -t $(CONTAINER_NAME) ./webserver

run:
	sudo docker run -dit --name $(CONTAINER_NAME) \
	 	-p $(PORT):80 \
		-v $$(pwd)/webserver/public_html:/var/www/html \
		-v $$(pwd)/webserver/backend:/backend \
		$(CONTAINER_NAME)

clean: 
	sudo docker stop $(CONTAINER_NAME)
	sudo docker rm $(CONTAINER_NAME)
