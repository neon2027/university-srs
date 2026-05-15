# Prompt: Bicol University Service Request System (Overall)

**Role:** Expert Full-Stack Developer (TALL Stack & Filament Specialists)
**Task:** Build a dual-sided Service Request System (BUSRS) consisting of a mobile-first Client Portal and a robust Filament-powered Admin Panel.

---

## 1. Project Architecture
*   **Backend:** Laravel 11.
*   **Admin Panel:** Filament v3.
*   **Client Frontend:** Livewire + Tailwind CSS (Custom UI).
*   **Hosting/Storage:** Hostinger Cloud / VPS.
*   **Authentication:** University Google OAuth integration.

---

## 2. Client Portal (Student/Personnel View)
### A. Request Submission
*   Multi-step form using Livewire.
*   Dynamic fields that update based on the selected Office.
*   Automated compression for all file attachments (PDFs/Images) to optimize server storage.

### B. Real-Time Tracking & Messaging
*   **Status Timeline:** A visual, vertical progress tracker for every ticket.
*   **Customer Service Chat:** 
    *   Real-time messaging with office staff.
    *   Optimistic UI updates and "Seen" indicators.
    *   Support for inline file attachments.

---

## 3. Admin Panel (Staff/Office View)
### A. Ticket Management (Filament Resources)
*   **Dashboard:** High-level overview of pending, forwarded, and resolved tickets.
*   **Staff Assignment:** Action to assign tickets to specific clerical personnel with automatic app/email notifications.

### B. Inter-Office Forwarding (Feature #8)
*   **Forward Action:** Ability to transfer a ticket from Office A to Office B.
*   **Credit Logic:** Upon forwarding, the recipient office (Office B) must choose:
    1. **Accept Credit:** The ticket contributes to the performance metrics of both offices.
    2. **Reference Only:** The credit remains with the originating office.

### C. Advanced Messaging Module
*   **Internal Notes:** Toggle for staff-only messages invisible to the student.
*   **Canned Responses:** Dropdown for common university replies.
*   **Staff Indicators:** Shows which staff member is currently typing or responding.

---

## 4. Technical Requirements & Workflow
### A. Database & Logic
*   Implement a `ticket_history` table to power the **Status Timeline**.
*   Implement a `forwarding_logs` table to track "Credit" logic across offices.
*   Use Role-Based Access Control (RBAC) to restrict staff to their specific office tickets.

### B. File Optimization
*   Use `Intervention Image` or `spatie/laravel-medialibrary`.
*   Enforce a mandatory compression pipeline for all uploads to maintain server health on Hostinger.

### C. Real-Time Logic & Deployment
*   **WebSockets:** Use Laravel Reverb. 
*   **Hostinger Specifics:** Configure the environment to support a persistent WebSocket server (Supervisor/Systemd) if on a VPS, or utilize Livewire `wire:poll` as a high-performance fallback for shared hosting environments.