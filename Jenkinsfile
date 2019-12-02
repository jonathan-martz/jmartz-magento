pipeline {
    agent any

    stages {
        stage('Install requirements') {
            steps {
                sh 'robo composer:install'
            }
        }
        stage('Download Config: Master') {
            when {
                branch "master"
            }
            steps {
                sh 'robo  download:config-production'
            }
        }
        stage('Download Config: Develop') {
            when {
                branch "develop"
            }
            steps {
                sh 'robo download:config-develop'
            }
        }
        stage('Magento2 Setup') {
            steps {
                sh 'cd src && bin/magento setup:upgrade'
                sh 'cd src && bin/magento setup:di:compile'
                sh 'cd src && bin/magento setup:static-content:deploy'
            }
        }
        stage('Phan') {
            steps {
                sh 'robo phan:check'
            }
        }
        stage('Deploy: Master') {
            when {
                branch "master"
            }
            steps {
                sh 'robo deploy:production'
            }
        }
        stage('Deploy: Develop') {
            when {
                branch "develop"
            }
            steps {
                sh 'robo deploy:develop'
            }
        }
    }
}
