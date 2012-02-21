set :stages, %w(staging pre_production production)
set :default_stage, "staging"
require 'capistrano/ext/multistage'