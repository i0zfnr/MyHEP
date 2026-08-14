# Role and access matrix

Authorization is enforced by session-role middleware, `EnsureAdminScope`, lecturer page access, feature flags, and ownership checks. UI visibility is not an authorization boundary.

| Capability | Lecturer | Scholarship admin | Discipline admin | Head JHEP | Guard | System admin |
|---|---:|---:|---:|---:|---:|---:|
| Scholarship and welfare | Conditional category | Yes | No | Yes | No | Yes |
| Discipline | Conditional category | No | Yes | Yes | No | Yes |
| Register offense | Yes | No | Yes | Yes | No | Yes |
| Student list | No | Yes | Yes | Yes | Yes | Yes |
| Student lookup | Yes | Yes | Yes | Yes | Yes | Yes |
| Sensitive student detail | No | Yes | Yes | Yes | No | Yes |
| Student export | No | No | Yes | Yes | No | Yes |
| Student create/update/delete | No | No | Yes | Yes | No | Yes |
| Movement | Conditional category | No | Yes | Yes | Yes | Yes |
| Reports | Yes | Yes | Yes | Yes | No | Yes |
| Student documents administration | No | No | Yes | Yes | No | Yes |
| Staff management | No | No | No | Yes | No | Yes |
| Laptop use | Yes | Yes | Yes | Yes | Yes | Yes |
| Laptop inventory management | No | No | No | Yes | No | Yes |
| System configuration | No | No | No | No | No | Yes |

Program records are visible to staff according to the program workspace. Editing and operational control are restricted to the program director/owner or an authorized system role. Review actions are available only to the reviewer assigned for the current report stage.

Sensitive student fields and private files must never be exposed through public storage URLs. Scholarship administrators may view the information required for scholarship and welfare duties but do not receive student mutation or export rights unless separately authorized.

