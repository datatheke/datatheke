#!/bin/sh

composer run-script post-update-cmd
apache2ctl -DFOREGROUND
