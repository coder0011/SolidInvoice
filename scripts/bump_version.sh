#!/bin/bash

set -euxo pipefail

# GitHub Actions setup for Git
if [ -n "$GITHUB_ACTIONS" ]; then
  git config --local user.email "action@github.com"
  git config --local user.name "GitHub Action"
  git remote set-url origin https://"${GITHUB_ACTOR}":"${GH_TOKEN}"@github.com/"${GITHUB_REPOSITORY}".git
fi

BRANCH=${1:-}
VERSION=${2:-}

# File path
FILE="./src/CoreBundle/SolidInvoiceCoreBundle.php"
PACKAGE_JSON="./package.json"
COMPOSER_JSON="./composer.json"

# Function to bump semver
bump_version() {
  local version=$1
  local field=$2
  local increment=$3

  IFS='.' read -ra parts <<< "$version"
  ((parts[field]+=increment))

  if [ "$field" -lt 2 ]; then
    for i in $(seq $((field+1)) 2); do
      parts[i]=0
    done
  fi

  echo "${parts[0]}.${parts[1]}.${parts[2]}"
}

# Check for version argument
if [ "$VERSION" ]; then
  next_version="$VERSION"
else
  current_version=$(awk -F\' '/public const VERSION/ {print $2}' $FILE)
  clean_version="${current_version%-dev}"
  next_version=$(bump_version "$clean_version" 2 1)  # bumping minor version
fi

# Update file with bumped version
sed -i "s/public const VERSION = '.*';/public const VERSION = '$next_version';/" $FILE
jq --arg version "$next_version" '.version=$version' --indent 4 $PACKAGE_JSON > tmp.json && mv tmp.json $PACKAGE_JSON
jq --arg version "$next_version" '.version=$version' --indent 4 $COMPOSER_JSON > tmp.json && mv tmp.json $COMPOSER_JSON

composer update --lock

if [ "${RELEASE:-}" = "1" ]; then
    git add $FILE $PACKAGE_JSON $COMPOSER_JSON composer.lock
    git commit -m "Release version $next_version"
    git push origin "${BRANCH}"
fi
