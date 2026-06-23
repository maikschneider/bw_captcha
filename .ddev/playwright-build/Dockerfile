#ddev-generated
ARG PLAYWRIGHT_DOCKER_IMAGE=ghcr.io/xima-media/playwright:1.59.1
ARG KASMVNC_VERSION=1.4.0
ARG KASMVNC_DIST=noble
FROM ${PLAYWRIGHT_DOCKER_IMAGE}

ARG PLAYWRIGHT_DOCKER_IMAGE
ARG KASMVNC_VERSION
ARG KASMVNC_DIST
LABEL PARENT_IMAGE="${PLAYWRIGHT_DOCKER_IMAGE}"

RUN mv /etc/apt/apt.conf.d/docker-clean /etc/apt/docker-clean-disabled

RUN deluser --remove-home pwuser || true
RUN deluser --remove-home ubuntu || true
ARG username
ARG uid
ARG gid
RUN getent group tty || groupadd tty
RUN (groupadd --gid "$gid" "$username" || groupadd "$username" || true) && \
    (useradd -G tty -l -m -s "/bin/bash" --gid "$username" --comment '' --uid "$uid" "$username" || \
    useradd -G tty -l -m -s "/bin/bash" --gid "$username" --comment '' "$username" || \
    useradd -G tty -l -m -s "/bin/bash" --gid "$gid" --comment '' "$username" || \
    useradd -G tty -l -m -s "/bin/bash" --comment '' "$username")

ENV CAROOT=/mnt/ddev-global-cache/mkcert

RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    apt-get update \
    && apt-get install -y sudo mariadb-client

RUN mkdir -p /etc/sudoers.d && \
    echo "$username ALL=(ALL) NOPASSWD: ALL" > "/etc/sudoers.d/$username" && \
    chmod 0440 "/etc/sudoers.d/$username"

ARG TARGETARCH
RUN mkdir -p /usr/local/bin && \
    curl --fail -JL -s -o /usr/local/bin/mkcert "https://dl.filippo.io/mkcert/latest?for=linux/${TARGETARCH}" && \
    chmod +x /usr/local/bin/mkcert

RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    apt-get update \
    && apt-get install -y icewm xauth

RUN apt-get update && apt-get install -y software-properties-common && \
    add-apt-repository -y universe && apt-get update

RUN wget https://github.com/kasmtech/KasmVNC/releases/download/v${KASMVNC_VERSION}/kasmvncserver_${KASMVNC_DIST}_${KASMVNC_VERSION}_${TARGETARCH}.deb && \
    apt-get install -y ./kasmvncserver*.deb && \
    rm -f kasmvncserver*.deb

RUN mv /etc/apt/docker-clean-disabled /etc/apt/apt.conf.d/docker-clean

RUN mkdir -p "/home/$username/.vnc"
COPY kasmvnc.yaml xstartup /home/"$username"/.vnc/
RUN touch "/home/$username/.vnc/.de-was-selected"
RUN sudo -u "$username" /bin/bash -c "echo -e \"secret\nsecret\n\" | kasmvncpasswd -wo -u \"$username\""
RUN chown -R "$uid:$gid" "/home/$username"
RUN usermod -a -G ssl-cert "$username"

COPY entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
