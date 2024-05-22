shell:
	docker run --rm -it -v ./:/var/www/html 940022816489.dkr.ecr.eu-west-1.amazonaws.com/lycan-base:latest /bin/bash

test:
	docker run --rm -it -v ./:/var/www/html 940022816489.dkr.ecr.eu-west-1.amazonaws.com/lycan-base:latest composer test