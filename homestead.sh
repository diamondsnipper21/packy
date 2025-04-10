#! /bin/bash
# use this command : bash homestead
# dev@wolfeo.me / #g$Hk8s+Q9u42!a

printf "\e[33m(step 1/2) Starting homestead...\e[0m\n"
cd ~/Homestead
vagrant up

printf "\e[33m(step 2/2) Running npm...\e[0m\n"
cd ~/PhpstormProjects/packy
nvm use 10.16.0
export NODE_OPTIONS="--max-old-space-size=8192"
npm run watch

printf "\e[32mDONE : Homestead is now ready, url: https://example.local\e[0m\n"

# reboot vagrant :
vagrant reload --provision

# vagrant ssh
# exit ssh : CTRL + D
