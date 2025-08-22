bashweb:
	docker-compose run --rm web /bin/sh

logsweb:
	docker-compose logs -f --tail=200 web
