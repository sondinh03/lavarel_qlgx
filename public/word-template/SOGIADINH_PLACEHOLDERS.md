# Biến mẫu Sổ gia đình công giáo

File: `public/word-template/sogiadinhconggiao_1-1-001.docx`  
Backup gốc: `sogiadinhconggiao_1-1-001.original.docx`

Xuất từ trang chi tiết gia đình → **Xuất sổ GĐCG**  
Route: `families.export-sogiadinh`

## Đã gắn trong mẫu (51 biến từ dữ liệu mẫu)

### Header / hộ
| Biến | Ý nghĩa |
|------|---------|
| `${diocese}` | Giáo phận |
| `${deanery}` | Giáo hạt |
| `${parish}` | Giáo xứ |
| `${parish_group}` | Giáo họ |
| `${family_code}` | Mã gia đình |
| `${head_name}` | Chủ hộ |
| `${contact}` | Địa chỉ + SĐT |
| `${joined_date}` | Ngày nhập xứ |
| `${member_count}` | Số thành viên (vd `3/3`) |
| `${married_date}` | Ngày kết hôn |

### Hôn phối
| Biến | Ý nghĩa |
|------|---------|
| `${husband_name}` `${wife_name}` | Bên nam / nữ |
| `${husband_birthday}` `${wife_birthday}` | Ngày sinh |
| `${husband_baptism_parish}` `${wife_baptism_parish}` | Nơi rửa tội |
| `${husband_confirmation_parish}` `${wife_confirmation_parish}` | Nơi thêm sức |
| `${husband_origin_parish}` `${wife_origin_parish}` | Dòng giáo xứ gốc |
| `${married_parish}` `${married_diocese}` | Nơi / GP hôn phối |
| `${marriage_priest}` `${pastor_name}` | LM chứng hôn / chánh xứ |

### Thành viên 1–3 (đã có dữ liệu mẫu)
`${m1_name}` `${m1_role}` `${m1_birthday}` `${m1_residence_status}`  
`${m1_baptism_parish}` `${m1_communion_parish}` `${m1_confirmation_parish}`  
`${m1_baptism_giver}` `${m1_confirmation_giver}`  
(tương tự `m2_*`, `m3_*`; `m3_father` / `m3_mother`)

## Nên bổ sung thủ công trong Word (ô còn trống)

Mở file Word, gõ đúng `${tên_biến}` vào chỗ trống:

### Kỷ niệm
`${father_birthday}` `${mother_birthday}`  
`${father_saint_day}` `${mother_saint_day}` `${parents_wedding_day}`  
`${father_death_date}` `${mother_death_date}`  
`${paternal_grandfather_death}` `${paternal_grandmother_death}`  
`${maternal_grandfather_death}` `${maternal_grandmother_death}`

### Hôn phối (ô trống)
`${husband_birth_place}` `${wife_birth_place}`  
`${husband_baptism_date}` `${wife_baptism_date}`  
`${husband_confirmation_date}` `${wife_confirmation_date}`  
`${husband_father}` `${husband_mother}` `${wife_father}` `${wife_mother}`  
`${marriage_witness_1}` `${marriage_witness_2}` `${marriage_certificate_number}`

### Thành viên m1–m7 (ô trống)
`${mN_index}` `${mN_role}` `${mN_name}` `${mN_birthday}` `${mN_birth_place}`  
`${mN_father}` `${mN_mother}` `${mN_residence_status}`  
`${mN_baptism_date}` `${mN_baptism_parish}` `${mN_baptism_giver}` `${mN_baptism_sponsor}` `${mN_baptism_number}`  
`${mN_communion_date}` `${mN_communion_parish}`  
`${mN_confirmation_date}` `${mN_confirmation_parish}` `${mN_confirmation_giver}` `${mN_confirmation_sponsor}` `${mN_confirmation_number}`

> Export đã map đủ các biến trên từ DB; biến chưa có trong Word sẽ bị bỏ qua (không lỗi).
