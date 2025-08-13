```mermaid
flowchart TD
    A([Start]) --> B[Load Calendar View]
    B --> C{User Action?}
    C -- Prev/Next Month --> D[Update Calendar View]
    C -- Refresh --> B
    C -- Change View Mode --> E[Switch View (Month/Week/Day/List)]
    C -- Click Event --> F[Display Event Preview Modal]
    D --> C
    E --> C
    F --> G{Close Modal?}
    G -- Yes --> C
    C -- No Action --> H([End])
