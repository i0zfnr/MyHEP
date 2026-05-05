# UAT Checklist (MyHEP POLIBESUT)

## 1) Authentication
- [ ] Student login with `matric_no + ic_no` (default) works.
- [ ] Student login with custom password works.
- [ ] Wrong credential shows error.
- [ ] Login throttling blocks after repeated failures.
- [ ] Admin login by IC works.
- [ ] Admin login by full name works.
- [ ] Logout works for all roles.

## 2) Role Access
- [ ] `scholarship_admin` can access scholarship modules only.
- [ ] `discipline_admin` can access discipline modules only.
- [ ] `system_admin` can access both + admin user management.
- [ ] Student cannot access admin routes.

## 3) Student Flow
- [ ] Dashboard index shows 3 portal cards.
- [ ] Scholarship module pages load and sidebar shows scholarship menu only.
- [ ] Offense module pages load and sidebar shows discipline menu only.
- [ ] Student can apply fine payment.
- [ ] Student can apply vehicle sticker.
- [ ] Student profile update + password change work.

## 4) Discipline Admin Flow
- [ ] Create/edit/delete offense.
- [ ] Mark offense paid.
- [ ] Fine application approve/reject + meeting date.
- [ ] Vehicle sticker approve/reject.
- [ ] Rules CRUD.
- [ ] Discipline announcements CRUD.

## 5) Scholarship Admin Flow
- [ ] Scholarship records CRUD.
- [ ] Scholarship announcements CRUD.

## 6) Reports/Exports
- [ ] CSV export works for: students, offenses, scholarships, fine applications, vehicle stickers, rules, scholarship announcements, discipline announcements.
- [ ] Monthly report page shows correct month data.

## 7) Audit/Security
- [ ] Critical actions create `audit_logs` rows.
- [ ] Reset password actions are logged.
- [ ] Approve/reject actions are logged.
- [ ] Delete actions are logged.
