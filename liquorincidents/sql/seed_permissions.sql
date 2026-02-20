USE bractools;

INSERT IGNORE INTO roles (role_key, label) VALUES
('staff','Staff'),
('admin','Admin'),
('manager','Manager'),
('lifeguard','Lifeguard'),
('facility_attendant','Facility Attendant');

INSERT IGNORE INTO permissions (perm_key, label) VALUES
('incidents.create','Create incidents'),
('incidents.view','View incident register'),
('incidents.edit','Edit incidents'),
('incidents.print','Print incidents/register'),
('incidents.export','Export incidents'),
('refusals.create','Create refusals'),
('refusals.view','View refusal register'),
('refusals.edit','Edit refusals'),
('refusals.print','Print refusals/register'),
('refusals.export','Export refusals'),
('admin.permissions.manage','Manage permissions'),
('admin.users.manage','Manage users');

INSERT IGNORE INTO role_permissions (role_key, perm_key, allowed) VALUES
('staff','incidents.create',1),
('staff','refusals.create',1),

('manager','incidents.create',1),
('manager','incidents.view',1),
('manager','incidents.edit',1),
('manager','incidents.print',1),
('manager','incidents.export',1),
('manager','refusals.create',1),
('manager','refusals.view',1),
('manager','refusals.edit',1),
('manager','refusals.print',1),
('manager','refusals.export',1),

('admin','admin.permissions.manage',1),
('admin','admin.users.manage',1),
('admin','incidents.create',1),
('admin','incidents.view',1),
('admin','incidents.edit',1),
('admin','incidents.print',1),
('admin','incidents.export',1),
('admin','refusals.create',1),
('admin','refusals.view',1),
('admin','refusals.edit',1),
('admin','refusals.print',1),
('admin','refusals.export',1);
