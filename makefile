install:
	composer install
	npm install
	npm run build

test:
	./vendor/bin/pest


reset_db:
	php artisan migrate:fresh --seed
