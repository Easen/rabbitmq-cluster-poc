# -*- mode: ruby -*-
# vi: set ft=ruby ts=2 sw=2 expandtab:

Vagrant.configure("2") do |config|

  box = "precise32"

  nodes = [
    { name: 'rabbit1', ip: '192.168.40.10', mgmt_port: 10010 },
    { name: 'rabbit2', ip: '192.168.40.11', mgmt_port: 10011 },
    { name: 'rabbit3', ip: '192.168.40.12', mgmt_port: 10012 },
    { name: 'rabbit4', ip: '192.168.40.13', mgmt_port: 10013 },
    { name: 'rabbit5', ip: '192.168.40.14', mgmt_port: 10014 },
    { name: 'rabbit6', ip: '192.168.40.15', mgmt_port: 10015 },
    { name: 'rabbit7', ip: '192.168.40.16', mgmt_port: 10016 },
    { name: 'rabbit8', ip: '192.168.40.17', mgmt_port: 10017 },
  ]

  nodes.each do |node|
    config.vm.define node[:name].to_sym do |rabbit_config|
      rabbit_config.vm.box = box
      if Vagrant.has_plugin?("vagrant-cachier")
        rabbit_config.cache.scope = :box
        rabbit_config.cache.synced_folder_opts = {
          type: :nfs,
          mount_options: ['rw', 'vers=3', 'tcp', 'nolock']
        }
      end

      rabbit_config.vm.network :forwarded_port, guest: 15672, host: node[:mgmt_port]
      rabbit_config.vm.network :private_network, ip: node[:ip]
      rabbit_config.vm.provision :shell, :path => "rabbitmq.sh"
      rabbit_config.vm.hostname = node[:name]
      rabbit_config.vm.synced_folder "src/", "/srv/"

      rabbit_config.vm.provision :shell, :path => "src/setup-federation.php"

    end
  end

  config.vm.define :worker do |worker_config|
    worker_config.vm.box = box
    worker_config.vm.network :private_network, ip: "192.168.64.20"
    worker_config.vm.provision :shell, :path => "worker.sh"
    worker_config.vm.hostname = 'worker'
    worker_config.vm.synced_folder "src/", "/srv/"
  end


end
