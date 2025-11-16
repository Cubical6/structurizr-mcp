workspace "Big Bank plc" "Internet Banking System for Big Bank plc" {

    model {
        person_1 = person "Personal Banking Customer" "A customer of the bank, with personal bank accounts" "External"
        system_1 = softwareSystem "Internet Banking System" "Allows customers to view information about their bank accounts, and make payments" {
            container_1 = container "Web Application" "Delivers the static content and the Internet banking single page application" "Java and Spring MVC"
            container_2 = container "API Application" "Provides Internet banking functionality via a JSON/HTTPS API" "Java and Spring Boot" {
                component_1 = component "Sign In Controller" "Allows users to sign in to the Internet Banking System" "Spring MVC Rest Controller"
                component_2 = component "Accounts Summary Controller" "Provides customers with a summary of their bank accounts" "Spring MVC Rest Controller"
            }
            container_3 = container "Database" "Stores user registration information, hashed authentication credentials, access logs, etc." "Oracle Database Schema" "Database"
        }
        system_2 = softwareSystem "Mainframe Banking System" "Stores all of the core banking information about customers, accounts, transactions, etc."
        system_3 = softwareSystem "Email System" "The internal Microsoft Exchange e-mail system"
        person_1 -> system_1 "Views account balances, and makes payments using" "HTTPS"
        system_1 -> system_2 "Gets account information from, and makes payments using" "XML/HTTPS"
        system_1 -> system_3 "Sends e-mail using" "SMTP"
        system_3 -> person_1 "Sends e-mails to"
        person_1 -> container_1 "Visits bigbank.com/ib using" "HTTPS"
        container_1 -> container_2 "Makes API calls to" "JSON/HTTPS"
        container_2 -> container_3 "Reads from and writes to" "JDBC"
        container_2 -> system_2 "Makes API calls to" "XML/HTTPS"
        container_2 -> system_3 "Sends e-mail using" "SMTP"
    }

    views {
        systemContext system_1 "SystemContext" {
            include *
            autoLayout lr
        }
        container system_1 "Containers" {
            include *
            autoLayout tb
        }
        component container_2 "Components" {
            include *
            autoLayout lr
        }

        styles {
            element "Software System" {
                background #1168bd
                color #ffffff
            }
            element "Person" {
                background #08427b
                color #ffffff
                shape person
            }
        }
    }
}
