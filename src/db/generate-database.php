<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\PHash;

$data = [
    '/images/03ae1fa1-b497-4d21-92ac-ccdf7cb8cbe9.webp',
    '/images/0c68ef4a-f0bf-4022-8605-7aee9d1cc127.webp',
    '/images/0cf775cb-c92f-474d-9a01-925dacacbca4.webp',
    '/images/0e71e52d-be87-4397-aeb7-1d528a8f6513.webp',
    '/images/10de6df4-4fbd-4dbd-bd54-0e5f97109cd1.webp',
    '/images/12218601-6303-49ca-8611-180dc1115e46.webp',
    '/images/1260367b-d046-4dae-91dc-fe0294130ab9.webp',
    '/images/15724a2d-3ef7-4e16-86b8-570f314bab13.webp',
    '/images/19e577f5-5490-4077-a82e-369f6fd8fb78.webp',
    '/images/1c2ea876-785b-4289-b86b-c435ceb0828f.webp',
    '/images/247616b5-1d3b-4777-abbb-d5e08a924b78.webp',
    '/images/26dd42ea-4c98-455e-ab53-e74db2fb1de2.webp',
    '/images/26e838a4-d5db-4245-98a1-41997f737b78.webp',
    '/images/389b709e-5102-4e55-aa5d-07099b500831.webp',
    '/images/4162cdef-ed2d-4dff-b777-e5afed5c4bd3.webp',
    '/images/441f9c14-6b81-43b1-a670-fc77d1317de2.webp',
    '/images/4abc2470-c465-4d87-ab0f-7f7e97b9c21c.webp',
    '/images/4f7bff75-6f86-4101-b629-3ed0b44b4b87.webp',
    '/images/5a3e2f3e-bfce-41c7-82b4-0ee8bc663295.webp',
    '/images/5ce0216a-3c5c-4518-b3a9-4731dd1de836.webp',
    '/images/5d60c2d2-cedb-4d2d-b524-c39bf2238d31.webp',
    '/images/5e2c1f44-7341-4385-b537-8f859c21f0c0.webp',
    '/images/6310e9a6-9cab-46fb-834d-06b2ea2967fb.webp',
    '/images/6d2f792b-b4f5-47b7-8f05-8358c8704679.webp',
    '/images/71e80796-373d-46fe-a161-088d7a1ca383.webp',
    '/images/7e4ff28a-8142-4848-b831-10ef1e5352f9.webp',
    '/images/81790969-727e-41d6-b821-50c49ad9af4c.webp',
    '/images/8dafdebb-863d-48a8-9224-614a192a3ef6.webp',
    '/images/8e158065-9466-46b0-9f94-32d15c271d0c.webp',
    '/images/8fbbf01a-084b-443f-97a2-df1fd57f9b5e.webp',
    '/images/9bc016bc-cd7a-49cc-a399-47930b00c59f.webp',
    '/images/9e72d835-10bf-4a43-a650-920709eaf01b.webp',
    '/images/a42a5d53-2f99-4e78-a081-9d07a2d0774a.webp',
    '/images/aad7fc25-7871-4263-93f7-644237bdd457.webp',
    '/images/ac871484-8738-44cf-abbd-4d347ad536e9.webp',
    '/images/ad942e4c-2070-463c-869a-5dab59867b39.webp',
    '/images/ae64253b-2b83-4d9d-ab16-d7dbb99dbb70.webp',
    '/images/afeb7d92-f558-4788-8ac7-7952e25f895f.webp',
    '/images/b032763f-bd34-4741-8967-658653e7ea5f.webp',
    '/images/b05afb11-db22-461d-b94e-49bdc316b445.webp',
    '/images/b2fb761d-5008-49c0-9ca8-edc30ec325a9.webp',
    '/images/b8992c10-41af-4da9-b2b7-2799e74a095d.webp',
    '/images/bddc0ff4-589a-4930-9e26-c5a75433a8e4.webp',
    '/images/c2643901-9e8c-41b5-b17d-36802f3102e9.webp',
    '/images/c3afc3ab-aae6-4185-b91e-e12dcfeaec90.webp',
    '/images/c872539d-b89d-4c8b-9896-ed5b6455b64a.webp',
    '/images/c8c8d1df-22e6-40fe-b7e7-39e6add42def.webp',
    '/images/daa7956d-d866-4f4a-a90a-32b94028782c.webp',
    '/images/db7c14f3-adbe-4eee-9534-d372808ce154.webp',
    '/images/de53aa36-9cec-421f-8978-4ada55821881.webp',
    '/images/e3fc1419-b3cf-4dcb-9398-2c2db3a4ce7c.webp',
    '/images/e527a8aa-768f-4f1b-ba8e-9f2fd8dc290b.webp',
    '/images/e5cbf0b6-b99d-42d5-8f8a-4bcfa6817e65.webp',
    '/images/e6da41fa-1be4-4ce5-b89c-22be4f1f02d4.webp',
    '/images/e83f26a0-7393-428a-aa9c-07d440cf6ade.webp',
    '/images/e93d7fd8-4ee7-4c49-804a-6940031b1f01.webp',
    '/images/f0f09613-0f37-4e3b-8b11-42fb741187c7.webp',
    '/images/f106b16d-f32a-42dd-a622-3725ab44e614.webp',
    '/images/f147a002-d8af-4ce1-acd8-6b7b90a0be83.webp',
    '/images/f52549ad-24d9-4c23-98fa-7052c11e47bc.webp',
];

$fp = fopen('/var/www/html/db/database.csv', 'w');

fputcsv($fp, ['id', 'image', 'name', 'category', 'models', 'price', 'phash']);

array_walk($data, function (&$filename, $i) use ($fp) {
    fputcsv($fp, [
        $i + 1,
        $filename,
        sprintf('Nike Air Force #%2d', rand(0000, 9999)),
        'Zapatillas - Hombre',
        sprintf('%d colores', rand(2, 5)),
        sprintf('%0.2f€', rand(8000, 20000) / 100),
        PHash::getHash(dirname(__DIR__) . '/public/' . $filename, PHash::COMP_METHOD_AVERAGE)
    ]);
});

fclose($fp);
