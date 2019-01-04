#!/bin/bash

VERSION=$1

sed -i -E "s/(APP_VERSION=)[0-9\.]+/\1${VERSION}/g" Dockerfile

git add Dockerfile
git commit -m "Bump version to: $VERSION"
