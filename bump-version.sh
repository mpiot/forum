#!/bin/bash

VERSION=$1

git flow feature start $VERSION

sed -i -E "s/(APP_VERSION=)[0-9\.]+/\1${VERSION}/g" Dockerfile

git add Dockerfile
git commit -m "Bump version to: $VERSION"
