workspace "E-commerce Platform" "A simple e-commerce platform architecture" {

    model {
        person_1 = person "Customer" "A customer who purchases products"
        person_2 = person "Administrator" "An administrator who manages the platform"

        system_1 = softwareSystem "E-commerce System" "Main e-commerce platform" {
            container_1 = container "Web Application" "Customer-facing web application" "React SPA"
            container_2 = container "API Gateway" "REST API for all operations" "Node.js Express"
            container_3 = container "Product Service" "Product catalog management" "Java Spring Boot"
            container_4 = container "Order Service" "Order processing" "Java Spring Boot"
            container_5 = container "Database" "Stores product and order data" "PostgreSQL"
        }

        system_2 = softwareSystem "Payment Gateway" "External payment processing" External
        system_3 = softwareSystem "Email Service" "Email notification service" External

        person_1 -> system_1 "Browses products and places orders"
        person_2 -> system_1 "Manages products and orders"

        container_1 -> container_2 "Makes API calls to" "HTTPS/REST"
        container_2 -> container_3 "Fetches product data from" "HTTPS/REST"
        container_2 -> container_4 "Processes orders via" "HTTPS/REST"
        container_3 -> container_5 "Reads from and writes to" "SQL/TCP"
        container_4 -> container_5 "Reads from and writes to" "SQL/TCP"
        container_4 -> system_2 "Processes payments via" "HTTPS/REST"
        container_4 -> system_3 "Sends order confirmations via" "SMTP"
    }

    views {
        systemContext system_1 "SystemContext" {
            include *
            autoLayout lr
        }

        container system_1 "Containers" {
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
