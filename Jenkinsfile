pipeline {
    agent any

    stages {
        stage('Generate Config: Master') {
            when {
                branch "master"
            }
            steps {
                sh 'robo generate:robo-config-develop'
            }
        }
        stage('Deploy: Develop') {
            when {
                branch "develop"
            }
            steps {
                sh 'robo generate:robo-config-develop'
            }
        }
        stage('Deploy') {
            steps {
                sh 'robo deploy'
            }
        }
        stage('Install requirements') {
            steps {
                sh 'robo composer:install'
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
        stage('Publish Version') {
            steps {
                sh 'robo deploy'
            }
        }
    }
}
