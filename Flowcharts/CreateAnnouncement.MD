```mermaid
flowchart TD
    A([Start]) --> B[Fill in Form: Title, Message, Date, Time]
    B --> C{Click Preview?}
    C -- Yes --> D[Show Preview]
    D --> B
    C -- No --> E{Click Post Announcement?}
    E -- No --> B
    E -- Yes --> F[Show Confirmation Modal]
    F --> G{Confirm Post?}
    G -- Yes --> H[Submit Form to PHP]
    H --> I[Save to Database]
    I --> J([End])
    G -- No --> B
