#############################################################
#	Application
#############################################################

set :application, "automatic"
set :deploy_to, "/data/sites/benelux/mail-automatic-production"

#############################################################
#	Settings
#############################################################

default_run_options[:pty] = true
ssh_options[:forward_agent] = true
set :use_sudo, false
set :scm_verbose, true

#############################################################
#	Servers
#############################################################

set :user, "automatic"
set :domain, "217.112.190.50"
set :port, 54051
server domain, :app, :web
role :db, domain, :primary => true

#############################################################
#	Git
#############################################################

set :scm, :git
set :branch, "master"
set :scm_user, 'dvdpost'
set :repository, "git@github.com:dvdpost/mail-automatic.git"
set :deploy_via, :remote_cache

#############################################################
#	Passenger
#############################################################
