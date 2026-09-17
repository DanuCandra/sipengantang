# AGENTS.md —SIPENGANTANG

> **READ THIS FILE FIRST.**
>
> This repository containsSIPENGANTANG, a Laravel web application for managing bird competition events, participant registration, manual payment verification, judge scoring, violations/disqualification, realtime judging progress, TOPSIS ranking, final results, registration proof PDFs, and winner certificates.
>
> This file is the operational entry point for AI coding agents such as Codex.
>
> **Do not start coding before understanding the repository documentation and the existing code.**

---

# 1. Product Identity

**Product Name:** GantangScore

**Formal Product Name:**

> Sistem Penjurian Kompetisi Gantangan Burung Menggunakan Metode TOPSIS

Primary product roles:

```text
Owner
Admin
Juri
Peserta
Pengunjung Publik
```

Core product flow:

```text
Owner creates Event
        ↓
Owner assigns Admin
        ↓
Admin configures Classes / Prizes / Judges
        ↓
Participant registers Bird
        ↓
Participant uploads Payment Proof
        ↓
Admin verifies Payment
        ↓
Judge scores Participant
        ↓
Warnings / Disqualification if needed
        ↓
Scores from Judges are averaged
        ↓
TOPSIS ranks participants per class
        ↓
Admin finalizes result
        ↓
Public result is published
        ↓
Winner receives Certificate PDF
```

---

# 2. Documentation Is Part of the Codebase

Before implementing a feature, read the documents relevant to it.

Expected documentation:

```text
docs/
├── PRD.md
├── Design.md
├── Architecture.md
├── Schema.md
├── TOPSIS.md          # when available
├── Testing.md         # when available
├── Realtime.md        # when available
├── Deployment.md      # when available
└── API.md             # when available
```

At minimum, always understand:

```text
PRD.md
Design.md
Architecture.md
Schema.md
```

If documentation lives in repository root instead of `docs/`, use the existing location.

Do not duplicate documentation simply because its location differs.

---

# 3. Documentation Authority

Use the following authority rules.

## Business Behavior

`PRD.md`

Examples:

- Who can do what.
- Competition workflow.
- Payment flow.
- Warning / disqualification behavior.
- TOPSIS business usage.
- What belongs to MVP.

## UI / UX

`Design.md`

Examples:

- Layout.
- Responsive behavior.
- Dashboard navigation.
- Colors.
- Typography.
- Components.
- Landing page.
- Juri scoring UI.
- Participant registration UI.

## Technical Architecture

`Architecture.md`

Examples:

- Modular monolith.
- Layer responsibilities.
- Action / Service pattern.
- Queue.
- Reverb.
- Transactions.
- Authorization.
- Final ranking workflow.

## Database Structure

`Schema.md`

Examples:

- Tables.
- Fields.
- Foreign keys.
- Indexes.
- Snapshot data.
- Relationships.
- Migration order.

## Feature-Specific Documents

If they exist:

```text
TOPSIS.md
Testing.md
Realtime.md
Deployment.md
API.md
```

These refine their specific area but must not silently contradict the primary documents.

---

# 4. Conflict Resolution

If two documents appear to conflict:

```text
Business semantics
→ PRD.md wins

UI behavior
→ Design.md wins

Architecture structure
→ Architecture.md wins

Database contract
→ Schema.md wins
```

If a newer explicit requirement from the project owner exists in code/repository documentation, use it and update affected docs.

Do not resolve an actual business ambiguity by guessing.

---

# 5. First Action for Every Task

Before changing code:

```text
1. Read the user's task.
2. Identify affected domain(s).
3. Read relevant docs.
4. Inspect existing implementation.
5. Inspect related tests.
6. Reuse existing patterns.
7. Plan the smallest complete change.
8. Implement.
9. Run targeted tests.
10. Run broader tests if risk is high.
11. Format/lint.
12. Report what changed.
```

Do not immediately generate files before inspecting the current repository.

---

# 6. Architecture Summary

GantangScore uses:

```text
Laravel Modular Monolith
+
Laravel Blade
+
MySQL
+
Laravel Reverb
+
Laravel Echo
+
Laravel Queue
+
Vite
```

Database is the source of truth.

Realtime is a delivery mechanism only.

```text
Request
↓
Validation
↓
Authorization
↓
Action / Domain Service
↓
Database Transaction
↓
Commit
↓
Domain Event
↓
Queue / Broadcast
↓
UI Update
```

---

# 7. Do Not Turn This Into a SPA

The frontend architecture is:

```text
Laravel Blade
+
progressive JavaScript
```

Alpine.js may be used for local UI interaction.

Do not replace the project with:

```text
React
Vue SPA
Next.js
Nuxt
Inertia
Livewire
```

unless explicitly requested.

Use Blade as the primary rendering layer.

---

# 8. Do Not Turn This Into Microservices

MVP is a modular monolith.

Do not introduce:

```text
microservices
Kafka
RabbitMQ
service mesh
Kubernetes
separate ranking service
separate payment service
```

without a new explicit architecture decision.

---

# 9. Core Domains

Organize business logic around these domains:

```text
Event
Registration
Payment
Judging
Ranking
Certificate
```

Suggested:

```text
app/Domain/
├── Event/
├── Registration/
├── Payment/
├── Judging/
├── Ranking/
└── Certificate/
```

Follow the actual repository structure if it already exists.

Do not restructure working code only to make folder names look perfect.

---

# 10. Controllers Must Stay Thin

Controller responsibility:

```text
receive request
authorize
call action/service
return response
```

Controller must not contain:

- TOPSIS formulas.
- long business queries.
- disqualification logic.
- ranking rules.
- complex state transitions.
- PDF generation internals.
- large broadcast orchestration.

---

# 11. Use Actions for Use Cases

Examples:

```text
CreateEventAction
AssignEventAdminAction
CreateCompetitionClassAction
CreateRegistrationAction
SubmitPaymentAction
VerifyPaymentAction
RejectPaymentAction
SaveEvaluationDraftAction
SubmitEvaluationAction
WarnParticipantAction
DisqualifyParticipantAction
RecalculateProvisionalRankingAction
FinalizeClassResultAction
GenerateCertificateAction
```

Actions represent business operations.

---

# 12. Use Services for Reusable Domain Logic

Examples:

```text
TopsisCalculator
RankingService
JudgingProgressService
ViolationRuleService
RegistrationNumberGenerator
CertificateRenderer
```

Avoid meaningless classes:

```text
HelperService
CommonService
UtilityService
ManagerService
```

Name classes by domain purpose.

---

# 13. Database Is the Source of Truth

Never rely on browser state or WebSocket events as authoritative state.

Correct:

```text
POST score
↓
database commit
↓
broadcast update
```

Incorrect:

```text
browser receives WebSocket
↓
assume score exists
```

The application must still work when Reverb is unavailable.

---

# 14. Realtime Stack

Realtime:

```text
Laravel Reverb
Laravel Echo
Laravel Broadcasting
Laravel Queue
```

Used for:

```text
judging progress
judge status
participant DQ updates
provisional ranking updates
final result availability
```

Do not use Reverb for writes.

All writes remain normal authorized Laravel requests.

---

# 15. Realtime Must Fail Gracefully

If Reverb disconnects:

```text
core application remains usable
```

UI should show:

```text
Connected
Reconnecting
Offline fallback
```

A lightweight polling fallback may be used while disconnected.

Do not make payment verification, judging, DQ, or finalization depend on a working WebSocket connection.

---

# 16. Queue

Queue is used for appropriate asynchronous jobs:

```text
broadcasts
provisional ranking recalculation
certificate generation
heavy PDF work
```

Default MVP backend:

```text
database queue
```

Redis is optional optimization.

Do not silently make Redis mandatory.

---

# 17. Authentication

Use Laravel session authentication.

Public registration is intended for:

```text
Peserta
```

Owner/Admin/Juri accounts may be created/assigned by authorized users according to product flow.

Do not create separate authentication systems for each role.

---

# 18. Global Roles

Supported roles:

```text
OWNER
ADMIN
JUDGE
PARTICIPANT
```

Roles belong to:

```text
user_roles
```

One user may have more than one role.

---

# 19. Global Role Does Not Grant Global Resource Access

This is critical.

A user with:

```text
ADMIN
```

does not automatically manage every event.

Admin access requires:

```text
event_admins
```

Judge access requires:

```text
judge_assignments
```

Participant resource access requires ownership.

---

# 20. Authorization Is Server-Side

Every protected operation needs:

```text
Policy
Gate
scoped query
ownership / assignment check
```

Do not treat hidden buttons or frontend visibility as authorization.

---

# 21. Prevent IDOR

For nested resources, verify the entire relationship chain.

Example:

```text
/admin/events/10/registrations/500
```

Requires:

```text
admin assigned to event 10
AND
registration 500 belongs to event 10
```

---

# 22. Database Core Tables

Current domain schema includes:

```text
users
user_roles
participant_profiles
events
event_admins
competition_classes
competition_class_criteria
prizes
birds
registrations
payment_submissions
judge_assignments
judge_evaluations
judge_scores
violations
ranking_runs
ranking_results
certificates
activity_logs
```

Do not invent duplicate tables when existing schema supports the requirement.

---

# 23. Do Not Create Separate User Tables

Do not create separate authentication tables for:

```text
owners
admins
judges
participants
```

All accounts use:

```text
users
```

with role/assignment relations.

---

# 24. Historical Data Is Important

Preserve:

```text
payment attempts
submitted scores
violations
DQ history
ranking runs
final ranking results
certificates
```

Do not hard-delete competition history for convenience.

---

# 25. Payment Flow

MVP payment is manual.

There is no payment gateway.

```text
Participant registers
↓
uploads proof
↓
WAITING_VERIFICATION
↓
Admin verifies or rejects
```

Payment proof table:

```text
payment_submissions
```

---

# 26. Never Overwrite Rejected Payment Proof

A registration may have:

```text
payment #1 → REJECTED
payment #2 → REJECTED
payment #3 → VERIFIED
```

Each attempt is a separate row.

---

# 27. Registration Fee Must Be Snapshotted

Class:

```text
registration_fee
```

Registration:

```text
registration_fee_snapshot
```

Future class price changes must not alter historical registrations.

---

# 28. Competition Structure

```text
Event
└── many Competition Classes
```

TOPSIS is calculated **per competition class**.

Never rank participants from different classes together.

---

# 29. Judging Criteria

MVP:

```text
C1 — Irama Lagu                     40%
C2 — Durasi Kerja / Kestabilan     20%
C3 — Volume                         20%
C4 — Gaya                           10%
C5 — Fisik                          10%
```

Score range:

```text
1..10
```

All criteria are:

```text
BENEFIT
```

---

# 30. Criteria Are Snapshotted Per Class

Use:

```text
competition_class_criteria
```

Historical classes must not depend only on a mutable global criteria template.

---

# 31. Judge Evaluation Structure

One evaluation:

```text
1 judge
+
1 registration
```

Header:

```text
judge_evaluations
```

Values:

```text
judge_scores
```

Only `SUBMITTED` evaluations affect ranking.

---

# 32. Submitted Evaluation Behavior

States:

```text
DRAFT
SUBMITTED
```

Current MVP behavior:

```text
SUBMITTED → read-only
```

Do not implement edit-after-submit until business rules explicitly allow it.

---

# 33. Judge Isolation

A judge should not see another judge's scores before their own submission.

Do not leak other judge scores into judge-facing queries or Blade views.

---

# 34. Warning and Disqualification

Warning-type examples:

```text
SHOUTING
PROVOCATION
DISTURBING
```

Current rule:

```text
first warning-type violation
→ WARNING

repeat warning-type violation
→ DISQUALIFICATION
```

---

# 35. Direct Disqualification

Examples:

```text
LATE_HANGING
NUMBER_MANIPULATION
```

These may immediately disqualify.

DQ requires a recorded reason and note.

---

# 36. Never Delete Scores After DQ

If participant is disqualified:

```text
scores remain
violation remains
registration becomes DISQUALIFIED
```

Participant is excluded from ranking input.

---

# 37. Concurrency Matters for Warning/DQ

Use transaction and row lock where necessary.

Do not count prior warnings outside a protected transaction then update state later.

---

# 38. TOPSIS Is Centralized

All TOPSIS math must live in:

```text
App\Domain\Ranking\Services\TopsisCalculator
```

or the already-established equivalent.

Do not duplicate formulas in:

```text
Controller
Blade
JavaScript
Job
Model accessor
Command
```

---

# 39. TOPSIS Input

For participant `i`, criterion `j`:

```text
xij = average of valid SUBMITTED judge scores
```

Draft scores do not count.

---

# 40. TOPSIS Formula

Normalize:

```text
rij = xij / √Σ(xij²)
```

Weighted normalization:

```text
yij = wj × rij
```

Positive ideal:

```text
A+ = max(yij)
```

Negative ideal:

```text
A- = min(yij)
```

Distances:

```text
Di+ = √Σ(yij - Aj+)²
Di- = √Σ(yij - Aj-)²
```

Preference:

```text
Vi = Di- / (Di+ + Di-)
```

Higher `Vi` means better ranking.

---

# 41. TOPSIS Must Be Deterministic

Same:

```text
scores
criteria
weights
algorithm version
```

must produce the same result.

Never use random tie-breaking.

---

# 42. Ranking Eligibility

Exclude:

```text
PENDING_PAYMENT
WAITING_VERIFICATION
PAYMENT_REJECTED
CANCELLED
DISQUALIFIED
```

Only valid registrations with valid submitted evaluations can enter ranking.

---

# 43. Provisional vs Final

During judging:

```text
PROVISIONAL
```

After explicit admin finalization:

```text
FINAL
```

Never label provisional data as official final result.

---

# 44. Finalization Recalculates From Source

Do not promote old provisional results blindly.

Correct:

```text
Admin Finalize
↓
validate
↓
load raw submitted scores
↓
average
↓
run TOPSIS
↓
create FINAL ranking run/results
↓
mark class FINALIZED
↓
commit
```

---

# 45. Final Results Are Immutable

After class is finalized, block changes affecting official ranking unless a formal future reopen flow is implemented.

Do not add an easy manual unfinalize action.

---

# 46. Ranking History

Final ranking must preserve:

```text
criteria snapshot
input snapshot
criterion averages
positive distance
negative distance
preference value
rank
algorithm
algorithm version
```

---

# 47. Never Recalculate Final Result on Public Page Load

Public completed result page reads stored FINAL ranking.

Do not run TOPSIS on every page request.

---

# 48. TOPSIS Edge Cases

Single eligible participant:

```text
rank = 1
```

Identical alternatives / zero meaningful distance:

```text
preference = 0.5
tie = true
```

Tie-break rule is still unresolved.

Do not invent a winner.

---

# 49. Design Direction

GantangScore should feel:

```text
professional
competitive
trustworthy
clean
natural
modern
sport-event oriented
```

Avoid:

```text
generic admin template
neon AI gradient
heavy glassmorphism
playful cartoon UI
```

---

# 50. Design System Snapshot

Primary:

```text
#276453
```

Dark primary:

```text
#173A31
```

Gold:

```text
#D9A441
```

Font:

```text
Inter
```

Use full token definitions from `Design.md`.

---

# 51. UI Must Reuse Components

Before creating new UI, search existing Blade components.

Common components:

```text
Button
Badge
Card
Modal
Input
Select
Textarea
Table
EmptyState
Progress
Alert
EventCard
RankingTable
JudgeProgress
```

Do not create inconsistent one-off styles.

---

# 52. Landing Page

Expected major sections:

```text
Navbar
Hero
Metrics
Ongoing Events
Upcoming Events
Why GantangScore
How It Works
Judging System
Judges
Completed Events
Registration CTA
Footer
```

Landing must not look like an admin panel.

---

# 53. Dashboard

Dashboard should be:

```text
functional
clear
fast
compact enough
```

Do not use oversized marketing sections inside operational dashboards.

---

# 54. Judge UI Is Mobile-First

Juri scoring must work well on phones.

Recommended scoring control:

```text
1 2 3 4 5 6 7 8 9 10
```

Do not use a slider for exact competition scores.

---

# 55. Status Must Be Clear

Use:

```text
text
badge
color
icon when helpful
```

Do not rely on color only.

---

# 56. Responsive Requirements

Check at minimum:

```text
360px
390px
768px
1024px
1280px
1440px
```

Tables should adapt into cards or suitable responsive structures.

---

# 57. Accessibility

Maintain:

```text
WCAG AA contrast
visible focus
real form labels
aria-label for icon buttons
semantic table headers
accessible modal focus
clear validation errors
```

---

# 58. Upload Security

Bird photo and payment proof:

```text
jpg/jpeg/png/webp
max around 5 MB
```

Validate MIME and size.

Generate safe filenames.

Do not trust original filenames.

---

# 59. Payment Proof Is Private

Payment proof must not be exposed through predictable public URLs.

Use private storage and authorized access.

---

# 60. Public Privacy Boundary

Public pages may show:

```text
event
class
bird name
bird photo
participant display name
gantangan number
competition progress
final result
```

Never expose publicly:

```text
email
phone
full address
payment proof
internal judge note
private audit data
```

---

# 61. PDF Documents

MVP:

```text
registration proof PDF
winner certificate PDF
```

Recommended templates:

```text
resources/views/pdf/registration-proof.blade.php
resources/views/pdf/certificate.blade.php
```

Only verified registrations receive registration proof.

Only FINAL winners receive certificates.

---

# 62. Transactions

Use transactions for:

```text
registration with quota check
payment verify/reject
score submit
warning escalation
DQ
finalization
```

---

# 63. Row Locking

Use row locking for concurrency-sensitive transitions such as:

```text
class quota
payment review
warning/DQ
evaluation submit
finalization
```

---

# 64. Migration Rules

Never edit an already-shared/deployed migration.

Create a new migration.

Never reset production data because schema changes are inconvenient.

---

# 65. Money Storage

Store Rupiah as integer.

Example:

```text
150000
5000000
```

Never FLOAT/DOUBLE.

UI:

```text
Rp150.000
Rp5.000.000
```

---

# 66. TOPSIS Storage

Use precise decimal storage for final numeric outputs.

Current schema recommends:

```text
DECIMAL(16,10)
```

Do not use FLOAT as authoritative result storage.

---

# 67. Enums

Use PHP backed enums for finite states.

Examples:

```text
UserRole
EventStatus
CompetitionClassStatus
RegistrationStatus
PaymentStatus
EvaluationStatus
CriterionType
PrizeType
ViolationAction
RankingRunType
```

Do not scatter raw state strings throughout the codebase.

---

# 68. No Queries in Blade

Do not execute Eloquent/DB queries inside Blade templates.

Prepare data in controller/query/service before rendering.

---

# 69. Avoid N+1

Use:

```text
eager loading
withCount
aggregate queries
select only necessary columns
```

especially on operational lists.

---

# 70. Paginate Large Lists

Use pagination for:

```text
events
participants
registrations
payments
activity logs
```

Do not use `Model::all()` for operational datasets.

---

# 71. Do Not Over-Engineer

Do not add by default:

```text
repository interface for every model
CQRS framework
custom DDD framework
custom ORM
generic CRUD engine
custom event bus
```

Use Laravel conventions.

---

# 72. MVP Non-Goals

Do not implement unless explicitly requested:

```text
payment gateway
WhatsApp notifications
email notifications
native mobile app
AI bird scoring
computer vision
live streaming
marketplace
affiliate system
```

---

# 73. Open Business Questions — DO NOT GUESS

The following are unresolved:

```text
same bird in multiple classes
tie-break rule
minimum judges
missing/absent judge behavior
editing submitted scores
reversing DQ
public provisional rank visibility
gantangan number assignment
waiting list behavior
certificate signature/stamp format
event-wide prizes outside classes
```

Do not silently encode an answer into schema or business logic.

---

# 74. Safe Defaults for Unresolved Questions

When implementation can continue safely:

```text
same bird multiple classes:
    keep schema permissive

tie:
    mark tied, do not invent winner

submitted score edit:
    keep immutable

public provisional ranking:
    show progress only

gantangan number:
    keep nullable

waiting list:
    do not implement

DQ reversal:
    do not implement silent reversal
```

---

# 75. Testing Is Mandatory

Important features require tests.

Depending on change:

```text
Unit
Feature
Authorization
Algorithm
Broadcast
```

Do not report feature complete if critical tests are missing.

---

# 76. TOPSIS Tests

`TopsisCalculator` should cover:

```text
known fixture
normalization
weighted matrix
positive ideal
negative ideal
distances
preference value
rank order
single participant
identical alternatives
ties
weight validation
```

---

# 77. Authorization Negative Tests

Test:

```text
unrelated admin → 403
unassigned judge → 403
participant accessing another participant → 403
cross-event resource → 403
```

---

# 78. Payment Tests

Test:

```text
upload
verify
reject
re-upload
history preservation
authorization
concurrent/repeated review safety
```

---

# 79. Judging Tests

Test:

```text
score range
all criteria required
draft excluded
submitted included
unverified excluded
DQ cannot be scored
duplicate evaluation prevented
```

---

# 80. DQ Tests

Test:

```text
first warning
repeated warning escalation
direct DQ
note required
score history preserved
ranking exclusion
```

---

# 81. Finalization Tests

Test:

```text
authorized admin
unauthorized admin
recalculate from raw source
exclude DQ
FINAL snapshot
class FINALIZED
certificate job
```

---

# 82. Realtime Tests

Test:

```text
event dispatched after state persistence
private channel authorization
public payload privacy
HTTP operation works without Reverb
```

---

# 83. Commands

Inspect repository scripts first:

```text
composer.json
package.json
phpunit.xml
vite.config.*
```

Typical commands may include:

```bash
composer install
npm install
php artisan migrate
php artisan db:seed
php artisan test
npm run build
```

Development may include:

```bash
php artisan serve
php artisan queue:work
php artisan reverb:start
npm run dev
```

Use repository-defined scripts if they differ.

---

# 84. Formatting / Static Analysis

Use existing tooling.

Possible:

```text
Laravel Pint
PHPStan / Larastan
ESLint
```

Do not add competing tooling unless requested.

---

# 85. Existing Code Has Priority Over Hypothetical Refactors

Inspect current implementation before changing architecture.

If current code has a valid established pattern compatible with docs:

```text
reuse it
```

Do not refactor unrelated areas just to match a theoretical structure perfectly.

---

# 86. Keep Changes Focused

If task is:

```text
fix payment validation
```

do not also:

```text
replace auth
redesign dashboard
rename all routes
rewrite TOPSIS
```

Prefer the smallest complete change.

---

# 87. Complete Vertical Slice

A new feature may require:

```text
migration
model
enum
request
policy
action/service
controller
route
Blade
event/job
tests
```

Implement the required layers together rather than leaving unfinished stubs.

---

# 88. Activity Logs

Important actions include:

```text
EVENT_CREATED
EVENT_UPDATED
ADMIN_ASSIGNED
REGISTRATION_CREATED
PAYMENT_SUBMITTED
PAYMENT_VERIFIED
PAYMENT_REJECTED
SCORE_SUBMITTED
WARNING_CREATED
PARTICIPANT_DISQUALIFIED
RESULT_FINALIZED
CERTIFICATE_GENERATED
```

Never log secrets.

---

# 89. Secrets

Never commit:

```text
.env
APP_KEY
database password
Reverb secret
private credentials
production tokens
```

---

# 90. State Changes Must Be Explicit

Do not create generic UI that lets users arbitrarily choose critical states.

Bad:

```text
<select name="status">
VERIFIED
DISQUALIFIED
FINALIZED
</select>
```

Use dedicated operations:

```text
verify payment
disqualify participant
finalize result
```

---

# 91. Event State

Typical:

```text
DRAFT
→ UPCOMING
→ ONGOING
→ COMPLETED
```

Cancellation:

```text
DRAFT/UPCOMING → CANCELLED
```

---

# 92. Registration State

```text
PENDING_PAYMENT
→ WAITING_VERIFICATION
→ VERIFIED
```

Rejected proof:

```text
WAITING_VERIFICATION
→ PAYMENT_REJECTED
→ WAITING_VERIFICATION
```

Violation:

```text
VERIFIED
→ DISQUALIFIED
```

---

# 93. Class State

```text
UPCOMING
→ ONGOING
→ JUDGING_COMPLETED
→ FINALIZED
```

Do not auto-finalize based only on time.

---

# 94. Evaluation State

```text
DRAFT
→ SUBMITTED
```

No normal backwards transition.

---

# 95. Realtime Privacy

Public broadcast payload must stay minimal.

Do not broadcast:

```text
payment details
private contact information
raw private judge notes
full internal ranking matrices
```

---

# 96. Search Before Adding Schema

Before adding table/field:

```text
search Schema.md
search migrations
search models
```

Ask:

```text
Is it already derivable?
Is it really required?
Is it a justified historical snapshot?
```

---

# 97. Do Not Add Redundant Data

Example:

Registration already reaches event through:

```text
registration
→ competition_class
→ event
```

Do not add `event_id` to registration without a strong reason.

---

# 98. JSON Use

Use relational tables for operational data.

Use JSON for:

```text
criteria snapshot
ranking input snapshot
criterion averages
activity metadata
```

Do not serialize judges/payments/scores into one large JSON blob.

---

# 99. Database History Rules

Never hard-delete or silently overwrite:

```text
submitted score
verified/rejected payment history
violation history
FINAL ranking
certificate record
```

---

# 100. Before Modifying Database

Read:

```text
Schema.md
existing migrations
models
factories
seeders
tests
```

Then create a safe new migration if needed.

---

# 101. Before Modifying TOPSIS

Read:

```text
Architecture.md
Schema.md
TOPSIS.md if present
TopsisCalculator
algorithm tests
fixtures
```

Run tests before and after.

---

# 102. Before Modifying UI

Read:

```text
Design.md
existing components
layouts
responsive rules
role-specific screens
```

Do not invent a new visual language.

---

# 103. Before Modifying Realtime

Read:

```text
Architecture.md
routes/channels.php
broadcast events
Echo setup
queue config
Reverb config
privacy rules
```

Ensure HTTP behavior still works when realtime is down.

---

# 104. Before Modifying Authorization

Check:

```text
global role
event assignment
class assignment
ownership
nested resource relationship
policy
negative tests
```

---

# 105. Before Modifying Payment

Remember:

```text
manual only
private proof
history preserved
event-scoped admin verification
transactional state sync
```

---

# 106. Before Modifying Judging

Remember:

```text
verified participant only
assigned judge only
1..10
all criteria required to submit
draft excluded
submitted immutable
judge isolation
```

---

# 107. Before Modifying DQ

Remember:

```text
create violation
change registration state
transaction
audit
ranking recalculation
preserve score history
```

---

# 108. Before Modifying Finalization

Required flow:

```text
authorize
lock
validate
recalculate
snapshot
persist
finalize
commit
broadcast
certificate job
```

Do not shortcut.

---

# 109. Definition of Done — General

```text
[ ] Requirement satisfied
[ ] Architecture respected
[ ] Authorization enforced
[ ] Validation added
[ ] Domain state consistent
[ ] UI follows Design.md
[ ] Responsive behavior considered
[ ] Tests updated
[ ] Relevant tests pass
[ ] Formatter/lint passes
[ ] No secret added
[ ] No unrelated changes
[ ] Docs updated if contract changed
```

---

# 110. Definition of Done — Database Change

```text
[ ] Migration
[ ] FK
[ ] Index
[ ] Unique constraint if needed
[ ] Model relationship
[ ] Cast
[ ] Factory
[ ] Validation
[ ] Test
[ ] Schema.md updated
```

---

# 111. Definition of Done — UI Change

```text
[ ] Existing layout/component reused
[ ] Design tokens respected
[ ] Loading state
[ ] Empty state
[ ] Error state
[ ] Success feedback
[ ] Mobile checked
[ ] Accessibility considered
[ ] Authorization reflected but not relied upon
```

---

# 112. Definition of Done — Critical Business Change

For payment, judging, DQ, finalization:

```text
[ ] transaction
[ ] concurrency considered
[ ] activity log
[ ] negative authorization test
[ ] failure state
```

---

# 113. Reporting After Work

Report:

```text
Implemented
Changed files
Tests run
Important notes
Open questions
```

Never claim tests passed if not run.

Never hide failures.

---

# 114. If Tests Fail

Classify:

```text
caused by current change
pre-existing
environment/infrastructure
```

Fix current-change failures.

Do not delete tests just to make output green.

---

# 115. If Docs and Code Drift

Do not blindly rewrite everything.

Compare intended state and implemented state.

Prefer:

```text
preserve data
make smallest safe reconciliation
update documentation if implementation is intentionally newer
```

---

# 116. If Requirement Is Ambiguous

Do not invent the business answer.

If possible:

```text
build neutral extensible structure
mark TODO/open question
continue unaffected work
```

Only request clarification when safe implementation truly depends on the missing decision.

---

# 117. Product-Specific Non-Negotiables

```text
1. TOPSIS is per competition class.
2. Judge scores are averaged before TOPSIS.
3. Draft scores do not count.
4. DQ participants are excluded from ranking.
5. DQ never erases score history.
6. Payment is manually verified.
7. Rejected payment proof remains historical.
8. Database is source of truth.
9. Final result is an immutable snapshot.
10. Reverb is optional for correctness.
11. Admin access is event-scoped.
12. Judge access is class-scoped.
13. Participant access is ownership-scoped.
14. No manual rank override.
15. Never guess unresolved rules.
```

---

# 118. Quick Context — Owner

Owner can:

```text
create events
manage owned events
assign multiple admins
create/select admin accounts
monitor events
```

---

# 119. Quick Context — Admin

Admin manages assigned events:

```text
event info
classes
prizes
participants
payments
judges
judge assignments
judging monitor
TOPSIS results
finalization
```

Admin cannot manage unrelated events.

---

# 120. Quick Context — Juri

Juri can:

```text
view assigned classes
score assigned participants
save draft
submit score
issue warning
issue DQ
```

Juri cannot manage unrelated event operations.

---

# 121. Quick Context — Peserta

Participant can:

```text
manage profile
manage birds
view events
register bird
upload proof
track verification
download registration proof
follow competition
view final result
download own certificate
```

---

# 122. Quick Context — Public

Public can:

```text
view upcoming events
view ongoing events
view completed events
view event detail
view allowed live progress
view final results
```

---

# 123. Repository Hygiene

Do not commit:

```text
vendor/
node_modules/
.env
private storage files
real database dumps
debug logs
```

Respect `.gitignore`.

---

# 124. Preserve Existing User Work

Never casually run destructive commands such as:

```text
git reset --hard
git clean -fd
php artisan migrate:fresh
php artisan db:wipe
DROP DATABASE
```

unless explicitly appropriate for a disposable development/test environment and required by the task.

Never destroy production-like data.

---

# 125. Locale

User-facing language:

```text
Bahasa Indonesia
```

Date:

```text
16 September 2026
```

Time:

```text
10.30 WIB
```

Money:

```text
Rp150.000
```

---

# 126. Source Code Naming

Prefer clear English code names:

```text
CompetitionClass
JudgeEvaluation
PaymentSubmission
RankingRun
FinalizeClassResultAction
```

UI copy can remain Bahasa Indonesia.

---

# 127. Comments

Use comments for:

```text
why
business rule
non-obvious algorithm behavior
```

Avoid comments that merely repeat obvious code.

---

# 128. TODO Format

Good TODO:

```text
TODO(product): Decide whether one bird may enter multiple
classes in the same event before adding uniqueness rules.
```

Bad:

```text
TODO fix later
```

---

# 129. Fake Production Metrics

Do not show invented statistics in production UI.

Landing metrics must come from database if shown as factual.

Seed/demo environments may use demo data.

---

# 130. Public Provisional Ranking Default

Because public provisional visibility is unresolved:

```text
default public live view = progress only
```

Do not expose provisional ranking publicly until explicitly enabled.

---

# 131. Ranking UI

Admin may see detailed TOPSIS internals.

Public/participant UI should prioritize understandable results.

Do not expose raw matrix complexity unless useful.

---

# 132. TopsisCalculator Design Goal

It should be:

```text
deterministic
mostly pure
database-independent
unit-testable
```

Database querying belongs outside the calculator.

---

# 133. Algorithm Version

Ranking runs should store:

```text
algorithm = TOPSIS
algorithm_version = 1.0
```

If algorithm semantics change, increment version.

Never mutate historical version.

---

# 134. Auditability Test

A final competition must be able to answer:

```text
Who participated?
Which bird?
Which class?
Who judged?
What scores were submitted?
Which criteria/weights were used?
Who was warned?
Who was disqualified?
Which input entered TOPSIS?
What preference was calculated?
What rank was produced?
Who finalized?
When?
Which certificate was generated?
```

Do not remove data needed to answer these questions.

---

# 135. AI Agent Working Style

Prefer:

```text
understand
reuse
extend
test
document
```

Avoid:

```text
replace
rewrite
invent
over-engineer
```

---

# 136. Final Instructions

If only a few rules are remembered, remember these:

> **Read the docs before coding.**

> **Inspect existing code before creating new patterns.**

> **Do not invent unresolved business rules.**

> **Database is the source of truth.**

> **Authorization is server-side and resource-scoped.**

> **Judge scores are averaged before TOPSIS.**

> **TOPSIS is calculated per competition class.**

> **Disqualification excludes a participant from ranking but never erases history.**

> **Final rankings are immutable and reproducible snapshots.**

> **Reverb improves realtime UX but is never required for correctness.**

> **Keep the Laravel + Blade modular monolith unless explicitly instructed otherwise.**
