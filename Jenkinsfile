pipeline {
    agent any

    stages {
        stage('Generate Config: Master') {
            when {
                branch "master"
            }
            steps {
                sh 'robo generate:robo-config-master'
            }
        }
        stage('Generate Config: Develop') {
            when {
                branch "develop"
            }
            steps {
                sh 'robo generate:robo-config-develop'
            }
        }
        stage('Remove Old Revisions start') {
            steps {
                sh 'robo remove:old-revisions'
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
                sh 'robo su'
                sh 'robo sdc'
                sh 'robo sscd'
                sh 'robo cacl'
                sh 'robo download:n98'
            }
        }
        stage('Phan') {
            steps {
                sh 'robo phan:check'
            }
        }
        stage('Publish Version') {
            steps {
                sh 'robo publish:version'
            }
        }
        stage('Remove Old Revisions end') {
            steps {
                sh 'robo remove:old-revisions'
            }
        }
    }
}
