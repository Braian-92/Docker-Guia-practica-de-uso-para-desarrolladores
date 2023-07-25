######## instalar virtual desde ###############
https://www.osboxes.org/ubuntu/
utilizar red bridgue sin replicar la red

######## instalar docker ###############
https://docs.docker.com/engine/install/ubuntu/

sudo apt-get update
sudo apt-get install ca-certificates curl gnupg

sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

echo \
"deb [arch="$(dpkg --print-architecture)" signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
"$(. /etc/os-release && echo "$VERSION_CODENAME")" stable" | \
sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt-get update

sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

sudo docker run hello-world

####### quitar dependencia sudo #####

https://docs.docker.com/engine/install/linux-postinstall/

sudo groupadd docker

sudo usermod -aG docker $USER

newgrp docker

docker run hello-world

################### docker base ##########

docker run hello-world (ejecutar 20 veces para test)

docker ps -a (viejo metodo)
docker container ls -a (nuevo metodo) listar contenedores (incluye detenidos "-a")

docker container rm 2aa540db6130 (eliminar contenedor)
docker container rm 2aa (se puede realizar con los primeros 3 char)

docker container rm 74e73e6d8b2a dd8623824444 (eliminar contenedores multiples por ID)

docker container prune (elimina todos los contenedores)

docker image ls
docker image rm hello-world
docker image ls

###### primer ejersicio ##########

docker container run docker/getting-started

ctrl + c (salir y terminar contenedor)

docker container run -d docker/getting-started (detach)

docker container stop ee1

docker container prune

docker container run -d -p 80:80 docker/getting-started (publish)

localhost (abrir navegador)


######### instalar docker desktop en ubuntu ###########
https://docs.docker.com/desktop/install/ubuntu/
descargar .deb

sudo apt install gnome-terminal
sudo apt remove docker-desktop

sudo apt-get update
cd Downloads
sudo apt-get install ./docker-desktop-4.21.1-amd64.deb

systemctl --user start docker-desktop (ERRRO no enciente) [fix prender VT-x/AMD-V en procesadores vmware]

https://stackoverflow.com/questions/76623028/unable-to-open-docker-desktop-on-ubuntu-running-on-vmware

Under System > Processor > Extended Features
    Enable PAE/NX
    Enable Nested VT-x/AMD-V

######### FIN instalar docker desktop en ubuntu ###########

docker container run -dp 8080:80 docker/getting-started (publish/detach)

localhost:8080

####### variables de entorno #########

https://hub.docker.com/_/postgres (bajar imagen postgres)
docker pull postgres

docker container run --name some-postgres -e POSTGRES_PASSWORD=mysecretpassword -d postgres (no tiene puerto de salida)

##### instalar table-plus ######
https://tableplus.com/blog/2019/10/tableplus-linux-installation.html
##### FIN instalar table-plus ######

docker container run --name some-postgres -dp 5432:5432 -e POSTGRES_PASSWORD=mysecretpassword postgres

conectar con tableplus
localhost : 5432
postgres
mysecretpassword

barra invertida -> "\" = (alt + 92)

docker container run \
--name labasededatos \
-e POSTGRES_PASSWORD=laclave \
-dp 5433:5432 \
postgres:11-bullseye (lo hacemos en otro puerto)

la idea es levantar las 2 imagenes y cargar los 2 postgres diferentes en 2 contenedores

buscamos el link de maria db en dockerhub pero no el tag lastest sino al nombre real (anterior)

docker pull mariadb:lts-jammy

docker container run \
--name mariadblocal \
-e MARIADB_RANDOM_ROOT_PASSWORD=yes \
-dp 3306:3306 \
mariadb:lts-jammy

docker container logs mariadblocal (visualizar los logs para ver la clave generada random) 
## SALIDA => 9[e:*Z"SuM(fP#(LU,yzxsa,Y2qe`_Yr


conectar con tableplus
127.0.0.1 : 3306 (si colocamos localhost da error)
root
9[e:*Z"SuM(fP#(LU,yzxsa,Y2qe`_Yr


######## conectar mariadb y phpmyadmin ##########

docker container run \
-dp 3306:3306 \
--name world-db \
--env MARIADB_USER=example-user \
--env MARIADB_PASSWORD=user-password \
--env MARIADB_ROOT_PASSWORD=root-secret-password \
--env MARIADB_DATABASE=world-db \
mariadb:lts-jammy

conectar con tableplus
127.0.0.1 : 3306
example-user
user-password

una vez dentro de table plus ejecutar el sql "archivos/cap03/world.sql"


########### VOLUMENES ############
########### VOLUMENES ############

docker volume create world-db

docker container run \
-dp 3306:3306 \
--name world-db \
--env MARIADB_USER=example-user \
--env MARIADB_PASSWORD=user-password \
--env MARIADB_ROOT_PASSWORD=root-secret-password \
--env MARIADB_DATABASE=world-db \
--volume world-db:/var/lib/mysql \
mariadb:lts-jammy

PD: el directorio "/var/lib/mysql" lo sacamos de la documentación de dockerhub

docker container rm -f eea (borramos el contenedor anterior forzosamente)

al eliminar y recrear el volumen este sigue contienendo la información guardada en la base de datos


##############  phpmyadmin  #################

https://hub.docker.com/_/phpmyadmin

docker container run \
--name phpmyadmin \
-d \
-e PMA_ARBITRARY=1 \
-p 8080:80 \
phpmyadmin:5.2.1-apache

## REDES ##

DOCU networks = https://docs.docker.com/engine/tutorials/networkingcontainers/

docker network ls (listamos las redes)

docker network create world-app (creamos una nueva red)

docker container ps -a (visualizamos los contenedores para sacar los IDs)
############################### SALIDA #######################
CONTAINER ID   IMAGE                     COMMAND                  CREATED          STATUS          PORTS                    NAMES
81ce76fd2c20   phpmyadmin:5.2.1-apache   "/docker-entrypoint.…"   About a minute ago   Up About a minute   0.0.0.0:8080->80/tcp     phpmyadmin
8f2f4c745d90   mariadb:lts-jammy         "docker-entrypoint.s…"   2 minutes ago        Up 2 minutes        0.0.0.0:3306->3306/tcp   world-db
############################### SALIDA #######################

docker network connect world-app 81c (unimos los contenedores a la red por el fragmento del ID)
docker network connect world-app 8f2 (unimos los contenedores a la red por el fragmento del ID)

docker network inspect world-app

########### (en este fragmento podremos ver los 2 contenedores enlazados)
Containers": {
    "3514baff29cfb4de524d0eddc88a83afa3b13e58dddbf6ef04e381d202def6bc": {
        "Name": "world-db",
        "EndpointID": "04054040f7847a36a5fc2790710413fc2f5b9beeb8f0ce56107f99b03aee01a7",
        "MacAddress": "02:42:ac:12:00:03",
        "IPv4Address": "172.18.0.3/16",
        "IPv6Address": ""
    },
    "d47f3ff9e8cc1bd7fcfa77066268663a854af540ab94ae99e2f089f4b769faad": {
        "Name": "phpmyadmin",
        "EndpointID": "3ca66ecb93af0cfab538f8423d86553e339c258a17c51f951bc91a9323dc4bd6",
        "MacAddress": "02:42:ac:12:00:02",
        "IPv4Address": "172.18.0.2/16",
        "IPv6Address": ""
    }
},
###########

IMPORTANTE: el nombre del contenedor pasa a ser como un DNS "world-db" = 172.18.0.3, el cual podremos usar para conectarnos desde phpmyadmin

ingresamos en phpmyadmin
localhost:8080 (en mi caso se cacheo y tengo que abrirlo en incognito [no se sacar en linux el cache])

world-db
example-user
user-password

##### agregar la red a la instancia #####

docker container run \
-dp 3306:3306 \
--name world-db \
--env MARIADB_USER=example-user \
--env MARIADB_PASSWORD=user-password \
--env MARIADB_ROOT_PASSWORD=root-secret-password \
--env MARIADB_DATABASE=world-db \
--volume world-db:/var/lib/mysql \
--network world-app \
mariadb:lts-jammy

docker container run \
--network world-app \
--name phpmyadmin \
-d \
-e PMA_ARBITRARY=1 \
-p 8080:80 \
phpmyadmin:5.2.1-apache

-----------
world-db
example-user
user-password
-----------

##### FIN agregar la red a la instancia #####

subir la carpeta "archivos/cap03/nest-graphql" al servidor linux (nueva carpeta "docker")

######### OPCIONAL INSTALAR WEBADMIN para gestionar el servidor desde el nevegador en windows de manera remota #########

sudo apt update
sudo apt install wget apt-transport-https
wget http://www.webmin.com/download/deb/webmin-current.deb
sudo dpkg -i webmin-current.deb
sudo apt --fix-broken install
https://TU_IP_UBUNTU:10000/

#########################

###### instalar net-tools ######
sudo apt-get install net-tools

ifconfig (192.168.1.44)

192.168.1.44:10000 (entrar con usuario y contraseña del servidor linux)

home/osboxes/docker
