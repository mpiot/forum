#!/bin/bash

docker tag $APP_IMAGE_NAME:$DEV_TAG $APP_IMAGE_NAME:dev-latest

docker push $APP_IMAGE_NAME:$DEV_TAG
docker push $APP_IMAGE_NAME:dev-latest
