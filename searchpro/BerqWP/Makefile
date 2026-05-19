SCOPER_PHAR := $(HOME)/.local/bin/php-scoper

.PHONY: install-scoper scope clean

install-scoper:
	mkdir -p "$(HOME)/.local/bin"
	curl -sL "https://github.com/humbug/php-scoper/releases/latest/download/php-scoper.phar" \
	    -o "$(SCOPER_PHAR)" && chmod +x "$(SCOPER_PHAR)"

scope:
	composer install --no-dev --no-interaction
	/opt/local/bin/php83 "$(SCOPER_PHAR)" add-prefix \
	    --config=scoper.inc.php \
	    --output-dir=vendor-prefixed \
	    --force --no-interaction


clean:
	rm -rf vendor-prefixed
