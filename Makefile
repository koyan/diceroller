bashflask:
	docker-compose run --rm flask /bin/sh

logsflask:
	docker-compose logs -f --tail=200 flask
