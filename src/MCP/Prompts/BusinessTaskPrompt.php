<?php

namespace Opscale\NovaMCP\MCP\Prompts;

/**
 * Business Tasks Prompt
 *
 * Explains how to perform business tasks - the actual work tasks
 * that users do in their platform (approve, send, process, complete, etc.).
 */
class BusinessTaskPrompt extends FactorsPrompt
{
    /**
     * The prompt name
     */
    public string $name = 'Performing Business Tasks';

    /**
     * The prompt description
     */
    public string $description = 'Learn how to execute your work tasks - approving, sending, processing, and completing business tasks';

    /**
     * A - Audience: Define the target audience
     */
    protected function audience(): string
    {
        return <<<'AUDIENCE'
**Primary Audience**: Platform users who need to perform their actual work tasks

**Examples of Users**:
- HR staff approving vacation requests and time sheets
- Sales teams sending invoices and quotes
- Finance staff processing payments and refunds
- Operations staff fulfilling orders and managing shipments
- Managers completing approvals and reviews

**Knowledge Level**: Understands their daily work tasks and what actions they take in the web application. Knows what needs to be in place before they can perform certain actions. No technical knowledge required.

**What They Care About**:
- What actions they can perform
- What needs to be ready before they can take an action
- The steps involved in completing their work
- What happens after they complete an action
- Handling situations when something goes wrong
AUDIENCE;
    }

    /**
     * C - Context: Provide background information
     */
    protected function context(): string
    {
        return <<<'CONTEXT'
**What This Is**:
Business actions are your actual work tasks - the things you do to get your job done. Unlike simply adding or viewing information, business actions perform work that affects your business processes.

**Types of Business Actions**:

**Approval Actions**:
- Approve vacation requests
- Approve purchase orders
- Approve time sheets
- Approve expense reports

**Processing Actions**:
- Process payments
- Process refunds
- Process returns
- Process applications

**Sending Actions**:
- Send invoices
- Send notifications
- Send reports
- Send confirmations

**Completion Actions**:
- Fulfill orders
- Complete shipments
- Complete projects
- Close tickets

**What Makes Them Different**:
Business actions are more than just updating information. When you perform a business action:
- Multiple things happen automatically
- Status changes occur
- Notifications may be sent
- Related records get updated
- Business rules are enforced

**Example - Sending an Invoice**:
When you send an invoice, the platform:
1. Verifies the client and items exist
2. Calculates totals and taxes
3. Generates the invoice document
4. Marks the invoice as "sent"
5. Sends an email to the client
6. Updates your records

**Prerequisites**:
Most business actions require certain information to exist first. Before sending an invoice, you need a client and items. If something's missing, you'll need to add that information first (managing records), then perform the action.
CONTEXT;
    }

    /**
     * T - Tone: Set the communication style
     */
    protected function tone(): string
    {
        return <<<'TONE'
**Action-oriented and clear**

- Focus on what gets accomplished, not how it works behind the scenes
- Use work-task language users know from their jobs
- Explain what needs to be ready before taking action
- Be clear about what will happen when the action completes
- Use natural flow: "First... then... and finally..."
- Make it feel like completing a familiar work task
- Connect actions to real business outcomes users care about
TONE;
    }

    /**
     * O - Objective: Define the specific goal
     */
    protected function objective(): string
    {
        return <<<'OBJECTIVE'
**Primary Goal**: Help users successfully perform their business actions by understanding:

1. What business actions are available in their platform
2. What information needs to exist before they can perform an action
3. The steps involved in completing each type of action
4. What happens after they complete an action
5. How to handle situations when prerequisites are missing
6. What results and notifications to expect

**Specific Deliverables**:
- Clear explanation of business actions with real work examples
- Prerequisites for common actions (what needs to exist first)
- Step-by-step guide for completing actions
- What to do when prerequisites are missing
- Expected outcomes and notifications
- Examples showing complete workflows from start to finish

**Success Metric**: User can identify what action they need to perform, ensure prerequisites are met, execute the action, and understand what happened as a result.
OBJECTIVE;
    }

    /**
     * R - Response format: Specify the output structure
     */
    protected function responseFormat(): string
    {
        return <<<'FORMAT'
Structure the guide as follows:

```markdown
# Performing Your Business Actions

## What Business Actions Are

[Explain how they differ from managing records - they complete actual work tasks]

## Types of Actions Available

### Approval Actions
[List examples: approve vacation, approve purchase order, etc.]

### Processing Actions
[List examples: process payment, process refund, etc.]

### Sending Actions
[List examples: send invoice, send notification, etc.]

### Completion Actions
[List examples: fulfill order, complete shipment, etc.]

## Before You Can Act: Prerequisites

[Explain that most actions require certain information to exist first]

**Common Prerequisites**:
- Client information must exist (for invoices, orders)
- Employee records must exist (for vacation approvals, time sheets)
- Products must exist (for orders, invoices)
- Original records must exist (for approvals, processing)

**If something's missing**: [Explain they need to add the information first]

## Complete Example: Sending an Invoice

**What you're trying to do**: Send an invoice to a client

**What needs to be ready**:
1. The client must exist in your system
2. The items/products must exist
3. Both must be active and valid

**The Steps**:

### Step 1: Make Sure the Client Exists
If Acme Corporation is not in your system yet, add them first:
```
Name: Acme Corporation
Email: contact@acme.com
```

### Step 2: Verify Your Items
Make sure the items you're billing for exist:
- Professional Services ($1,500)
- Monthly Subscription ($99)

### Step 3: Send the Invoice
With everything in place, perform the action:
```
Send Invoice to: Acme Corporation
Items:
  - Professional Services: $1,500
  - Monthly Subscription: $99
Total: $1,599
```

**What happens next**:
- ✓ Invoice is created and marked as "sent"
- ✓ Email is sent to contact@acme.com
- ✓ Invoice appears in your sent invoices
- ✓ Payment tracking begins
- ✓ You receive a confirmation

## Complete Example: Approving a Vacation Request

**What you're trying to do**: Approve an employee's vacation request

**What needs to be ready**:
1. The employee must exist
2. The vacation request must exist
3. The request must be in "pending" status

**The Steps**:

### Step 1: View the Request
Check the vacation request details:
```
Employee: John Smith
Dates: June 15-20, 2024
Days: 5 business days
Status: Pending
```

### Step 2: Approve the Request
Perform the approval action:
```
Approve vacation request for John Smith
Dates: June 15-20, 2024
```

**What happens next**:
- ✓ Request status changes to "approved"
- ✓ Email notification sent to John Smith
- ✓ Calendar updated with vacation days
- ✓ Team calendar shows John as away
- ✓ Remaining vacation balance updated

## Common Workflows

| What You Want to Do | Prerequisites Needed | What Happens |
|---------------------|---------------------|--------------|
| Send Invoice | Client, Items | Invoice sent, email sent, tracking begins |
| Approve Vacation | Employee, Request | Approved, notifications sent, calendar updated |
| Process Payment | Invoice, Payment Info | Payment recorded, invoice marked paid, receipt sent |
| Fulfill Order | Order, Inventory | Items shipped, inventory updated, tracking number generated |

## When Things Go Wrong

**"Client not found"**: Add the client first, then try sending the invoice again

**"Insufficient inventory"**: Check stock levels and restock items before fulfilling the order

**"Request already processed"**: This action was already completed - check the status

[More common situations with clear explanations and solutions]

## Quick Tips

1. Always check prerequisites before attempting an action
2. If something's missing, add it first (managing records), then perform the action
3. Wait for confirmation before assuming an action is complete
4. Actions that send emails or notifications may take a few moments
```

**Format Requirements**:
- Use everyday work language
- Show complete workflows from start to finish
- Include what users need to check first
- Explain what happens at each step
- Use checkmarks for completed actions
- Focus on familiar work tasks
- No technical terminology
FORMAT;
    }

    /**
     * S - Style: Define the presentation style
     */
    protected function style(): string
    {
        return <<<'STYLE'
**Story-driven and step-by-step**

- Walk through complete work scenarios users recognize
- Show the natural progression: prepare → act → result
- Use numbered steps for clarity
- Explain what's happening in business terms
- Include the "what happens next" for every action
- Make prerequisites obvious before the action
- Use checkmarks and visual cues for completion
- Keep it practical and task-focused
STYLE;
    }
}
