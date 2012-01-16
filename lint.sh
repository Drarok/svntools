#!/usr/bin/env bash

# Lint-check all PHP files.
find . -name '*.php' -exec php -l {} \;