1. 2 Table is mandatory - "capbac" (idcb (int11 - PRIMARY), tencapbac (text)) to provide userright lookup and "phanquyen" (tenbang(text - PRIMARY), viewright(text), insertright(text), updateright(text), deleteright(text)) to provide action authority lookup.
2. Primary key of type int should have auto_increment property.
----
﻿1. Hai bảng bắt buộc phải có: bảng "capbac" biểu thị các loại chức vụ và bảng "phanquyen" tra cứu chức vụ yêu cầu đối ứng với các chức năng tương ứng
2. Các bảng có khóa chính đều là dạng int với thuộc tính auto_increment.