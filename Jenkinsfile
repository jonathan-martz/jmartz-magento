pipeline {
    agent any

    stages {
        stage('Generate Config: Master') {
            when {
                branch "master"
            }
            steps {
                sh 'php -d memory_limit=1024MB /usr/bin/robo generate:robo-config-master'
            }
        }
        stage('Generate Config: Develop') {
            when {
                branch "develop"
            }
            steps {
                sh 'php -d memory_limit=1024MB /usr/bin/robo generate:robo-config-develop'
            }
        }
        stage('Remove Old Revisions start') {
            steps {
                sh 'php -d memory_limit=1024MB /usr/bin/robo remove:old-revisions'
            }
        }
        stage('Deploy') {
            steps {
                sh 'php -d memory_limit=1024MB /usr/bin/robo deploy'
            }
        }
        stage('Install requirements') {
            steps {
                sh 'php -d memory_limit=1024MB /usr/bin/robo composer:install'
            }
        }
        stage('Magento2 Setup') {
            steps {
                sh 'php -d memory_limit=1024MB /usr/bin/robo su'
                sh 'php -d memory_limit=1024MB /usr/bin/robo sdc'
                sh 'php -d memory_limit=1024MB /usr/bin/robo sscd'
                sh 'php -d memory_limit=1024MB /usr/bin/robo cacl'
                sh 'php -d memory_limit=1024MB /usr/bin/robo download:n98'
            }
        }
        stage('Phan') {
            steps {
                sh 'php -d memory_limit=1024MB /usr/bin/robo phan:check'
            }
        }
        stage('Publish Version') {
            steps {
                sh 'php -d memory_limit=1024MB /usr/bin/robo publish:version'
            }
        }
        stage('Remove Old Revisions end') {
            steps {
                sh 'php -d memory_limit=1024MB /usr/bin/robo remove:old-revisions'
            }
        }
    }
}
