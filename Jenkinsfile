pipeline {
    agent any

    stages {
        stage('Install requirements') {
            steps {
                sh 'robo composer:install'
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
