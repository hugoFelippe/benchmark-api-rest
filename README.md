
```shell

cd ./application && composer install && cd ../

docker build --pull --rm -f "docker/nginx/Dockerfile" -t benchmark:nginx "."
docker run --rm -it  -p 81:80/tcp benchmark:nginx

docker build --pull --rm -f "docker/ppm/Dockerfile" -t benchmark:ppm "."
docker run --rm -it  -p 82:80/tcp benchmark:ppm

```
