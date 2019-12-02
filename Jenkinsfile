pipeline {
    agent any

    stages {
        stage('Install requirements') {
            steps {
                sh 'robo composer:install'
            }
        }
        stage('Magento2 Setup') {
            steps {
                sh 'bin/magento setup:upgrade'
                sh 'bin/magento setup:di:compile'
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
