php-mem-managed
===============

[Eventually] Library to allow proper handling of potentially cyclic data structures without needing to impose ownership requirements on those systems of objects. This is to allow systems like Object Relational Mappers (ORMs) to keep the Single Responsibility Principle (SRP) when creating systems of objects that it doesn't own, that may contain cycles. This is not intended to be used as a project-wide memory manager. If the object owners are creating and destroying the objects, or they don't contain any cycles, PHP's built-in ref-counting mechanism should be sufficient for handling memory management properly. This library depends on the PECL WeakRefs package by Etienne Kneuss &lt;colder@php.net>.
