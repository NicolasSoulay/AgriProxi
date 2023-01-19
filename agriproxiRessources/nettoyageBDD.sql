select *
from entreprise; -- 1795

-- entreprise qui possède une adresse
select *
from entreprise e
where exists (select *
from adresse a
where e.id = a.entreprise_id); -- 988
-- --------------------

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


select *
from adresse a where 