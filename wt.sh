#!/bin/bash
# tmux ecc ecc.

echo Killing existing session of watchtower...
tmux kill-session -t wt

echo Starting tmux session...
tmux new-session -d -s "wt" protected/yiic wt
echo Ready!
