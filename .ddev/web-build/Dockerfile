
# You can copy this Dockerfile.example to Dockerfile to add configuration
# or packages or anything else to your webimage
ARG BASE_IMAGE
FROM $BASE_IMAGE
RUN npm install --global gulp-cli
RUN npm install -g aviglitch
RUN alias ll='ls -alF'
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add -
RUN apt update && DEBIAN_FRONTEND=noninteractive apt install -y -o Dpkg::Options::="--force-confold" --no-install-recommends --no-install-suggests ffmpeg

