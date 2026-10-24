# Changelog

All notable changes to this project will be documented in this file.

## 1.0.0 - 2026-10-24

First release.

- `Session` model on Laravel's database session table (follows `session.table` / `session.connection`), with `user()` relation and `active()`, `expired()`, `guests()`, `authenticated()`, `forUser()` scopes
- `decodePayload()`: plain, encrypted (`session.encrypt`) and JSON (`session.serialization`) payloads; objects are never instantiated; `_token` and `password_hash_*` masked by default
- `SessionManagement::revoke()`, `revokeUser()` and `prune()`
- `session-management:prune` command

## [Unreleased]
