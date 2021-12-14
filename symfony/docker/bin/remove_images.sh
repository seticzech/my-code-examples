#!/usr/bin/env bash

for image in $(docker images | grep "bb3_" | python -c 'import sys; print(reduce(lambda x,y: x+"\n"+y, [l.split()[0] for l in sys.stdin.readlines()]))'); do
    docker rmi $image
done