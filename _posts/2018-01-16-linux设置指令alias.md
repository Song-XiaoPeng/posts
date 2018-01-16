## Linux为指令添加别名alias

### linux中alias永久化的方法：
```
cd ~
ll -a
vim .bashrc
为你想要添加的指令设置
alias 别名name='你的指令'

  1 # .bashrc
  2 
  3 # User specific aliases and functions
  4 
  5 alias rm='rm -i'
  6 alias cp='cp -i'
  7 alias mv='mv -i'
  8 alias todb='mysql -uroot -proot'
  9 alias topub='cd /usr/share/nginx/html'
 10 alias tong='cd /etc/nginx/'
 11 alias tongconf='vim /etc/nginx/nginx.conf'
 12 alias totl='cd /usr/share/nginx/html/timeline/tp5/application/index/controller'
 13 alias totlapi='cd /usr/share/nginx/html/timeline/tp5'
 14 # Source global definitions
 15 if [ -f /etc/bashrc ]; then
 16         . /etc/bashrc
 17 fi

删除别名：
unalias name

查看alias：

列出目前所有的别名设置。
alias    或    alias -p

查看具体一条指令的别名
alias 别名name
eg: alias rm //alias cp='cp -i'

```

### 其他方式

`alias name='command line'` 这个修改只在该次登录的操作有效，只在当前的Shell生效。如果重新开启一个 Shell，或者重新登录，则这些alias将无法使用。

### note
- 若要每次登入就自动生效别名，则把别名加在/etc/profile或~/.bashrc中。然后# source ~/.bashrc
- 若要让每一位用户都生效别名，则把别名加在/etc/bashrc最后面，然后# source /etc/bashrc