-- Nettoyage des entreprises sans adresse

 -- entreprise sans adresse
select *
from entreprise e
where not exists (select *
from adresse a
where e.id = a.entreprise_id);-- 807

-- produits des entreprises sans adresse
select *
from produit p 
where p.entreprise_id in (select e.id
from entreprise e 
where p.entreprise_id = e.id 
and not exists (select *
from adresse a
where e.id = a.entreprise_id));

-- suppression des produits qui ont un entreprise qui n'ont pas d'adresse
delete
from produit 
where entreprise_id in (select e.id
from entreprise e 
where not exists (select *
from adresse a
where e.id = a.entreprise_id));-- 2

-- suppression des entreprise sans adresse
delete 
from entreprise
where id in (select e.id
from (select * from entreprise) as e
where not exists (select *
from adresse a
where e.id = a.entreprise_id));-- 807

-- Suppression des doublons
-- check des doublons produits
select max(id), concat(name,description,entreprise_id), count(concat(name,description,entreprise_id))
from produit
group by concat(name,description,entreprise_id)
having count(concat(name,description,entreprise_id))>1;-- 2666


-- suppression des doublons produits
delete
from produit
where id in (select max(id)
from (select * from produit) as p
group by concat(name,description,entreprise_id)
having count(concat(name,description,entreprise_id))>1);

-- check le nombre de produit final
select *
from produit;


-- Nettoyage des adresses sans coordonnées gps

-- marqueur des entreprise
update entreprise 
set description = concat(description,'nettoyageBDD')
where id in (select e.id
from (select * from entreprise) as e 
join adresse a on a.entreprise_id = e.id
where a.longitude =''
or a.latitude = '');-- 37

-- suppression produit ayant une adresse sans longitude et latitude
delete
from produit 
where id in (select p.id
from (select * from produit) as p 
where entreprise_id in (select e.id
from entreprise e
join adresse a on e.id = a.entreprise_id
where longitude =''
or latitude = ''));-- 93

-- suppression adresse sans longitude et latitude
delete
from adresse 
where id in (select a.id
from (select * from adresse) as a
join entreprise e on e.id = a.entreprise_id 
where longitude =''
or latitude = '');-- 61

-- suppression entreprise ayant une adresse sans longitude et latitude
delete
from entreprise
where id in (select e.id
from (select * from entreprise) as e
where e.description like '%nettoyageBDD');-- 37

-- Doublon d'adresse
select max(id)
from adresse a 
group by concat(label,entreprise_id,ville_id)
having count(concat(label,entreprise_id,ville_id))>1;

delete
from adresse 
where id in(select max(a.id)
from (select * from adresse) as a
group by concat(label,entreprise_id,ville_id)
having count(concat(label,entreprise_id,ville_id))>1);