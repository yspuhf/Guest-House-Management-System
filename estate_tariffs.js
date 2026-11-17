/**
 * Dr. Yashwant Singh Parmar University of Horticulture & Forestry, Nauni (Solan) - 173230 (H.P.)
 * Estate Organization - Central Guest House Tariff & Category Master
 * Ref: Notification No. UHF/EO-55/2025/- 1615-40, Dated: 25/7/2026
 *
 * This file manages the authoritative category definitions, stay charges (VVIP, VIP, Ordinary, Dormitory),
 * and Dining Hall charges. Changes made by the Estate Admin account are persisted in localStorage.
 */

const DEFAULT_ESTATE_NOTIFICATION = {
    docNumber: "UHF/EO-55/2025/- 1615-40",
    docDate: "25/07/2026",
    authority: "Hon'ble Vice Chancellor, Dr. Y.S. Parmar UHF Nauni (Solan)",
    issuer: "Estate Officer, Estate Organization",
    organization: "Estate Organization, Dr. YSP UHF Nauni (Solan) - 173230 (H.P.)"
};

const DEFAULT_ESTATE_CATEGORIES = [
    {
        id: "cat_1_official",
        srNo: "1(i)",
        title: "HP Govt / University Officers / ICAR / Central Govt Officials (On Official Visit)",
        description: "HP Govt./University Officers/Officials/MPs/MLAs of State, Judges & Judicial Officers of HP, Officers of Central Govt. Undertakings/Corporation/HPU/HPKV/HPTU/ICAR/ICFRE/Agri Universities/Central Govt. on official duty.",
        shortName: "HP Govt / University / ICAR Officers (Official)",
        vvip: 2500,
        vip: 1500,
        ordinary: 1000,
        type: "official"
    },
    {
        id: "cat_1_private",
        srNo: "1(ii)",
        title: "HP Govt / University / ICAR Officers (On Private Visit / While on Leave / Family)",
        description: "HP Govt./University/ICAR/State Officers on leave and his/her family member(s)/officials/former MPs/MLAs of the State on private visit.",
        shortName: "HP Govt / University / ICAR Officers (Private Visit)",
        vvip: 3000,
        vip: 2000,
        ordinary: 1500,
        type: "private"
    },
    {
        id: "cat_2_invited",
        srNo: "2",
        title: "Persons Invited by the University for University Work",
        description: "Persons invited by the University from other Universities/HP Govt. or other State Govt./ICAR/ICFRE/any other Institutes/Dept./NGOs for University work.",
        shortName: "Persons Invited by UHF for University Work",
        vvip: 2500,
        vip: 1500,
        ordinary: 1000,
        type: "official"
    },
    {
        id: "cat_3_parents",
        srNo: "3",
        title: "Parents / Guardians of Students Studying in UHF (incl. Nepal & Bhutan)",
        description: "Parents/Guardians/Family Members of students studying in this University including parents of students from Nepal and Bhutan.",
        shortName: "Parents / Guardians of UHF Students (incl. Nepal/Bhutan)",
        vvip: 2500,
        vip: 1500,
        ordinary: 1000,
        type: "institutional"
    },
    {
        id: "cat_4_other_official",
        srNo: "4(i)",
        title: "Officers / Officials from Other State Govts / Banks / PSUs (On Official Visit)",
        description: "Officers/Officials from other Govt./MPs/MLAs of other States, Judges and Judicial Officers of other State Govt., employees of Govt. Undertakings/Corporations and Banks on official visit.",
        shortName: "Other State Govts / Banks / PSUs Officers (Official)",
        vvip: 2500,
        vip: 1500,
        ordinary: 1000,
        type: "official"
    },
    {
        id: "cat_4_other_private",
        srNo: "4(ii)",
        title: "Officers / Officials from Other State Govts / Banks / PSUs (On Private Visit)",
        description: "Officers/Officials from other States while on leave and his/her family member(s)/officials/former MPs/MLAs of other States on private visit.",
        shortName: "Other State Govts / Banks / PSUs (Private Visit)",
        vvip: 3000,
        vip: 2000,
        ordinary: 1500,
        type: "private"
    },
    {
        id: "cat_5_nepal_bhutan",
        srNo: "5",
        title: "Delegates Visiting on Educational Tour & Training from Nepal & Bhutan",
        description: "Delegates visiting educational tour and training programmes from Nepal & Bhutan.",
        shortName: "Educational Tour Delegates from Nepal & Bhutan",
        vvip: 3500,
        vip: 2500,
        ordinary: 2000,
        type: "international"
    },
    {
        id: "cat_6_foreign_private",
        srNo: "6",
        title: "Foreign Delegates / Parents of Students (Other than Nepal & Bhutan)",
        description: "Foreign delegates/parents of students other than Nepal and Bhutan.",
        shortName: "Foreign Delegates / International Parents",
        vvip: 7000,
        vip: 5000,
        ordinary: 4000,
        type: "international"
    },
    {
        id: "cat_7_foreign_invited",
        srNo: "7",
        title: "Foreign Delegates Invited by the University",
        description: "Foreign delegates invited by the University for academic or administrative engagements.",
        shortName: "Foreign Delegates Invited by UHF",
        vvip: 7000,
        vip: 5000,
        ordinary: 4000,
        type: "international"
    },
    {
        id: "cat_8_press_official",
        srNo: "8(i)",
        title: "Press Correspondents & Media Personnel (Official Visits Invited by UHF)",
        description: "Press Correspondents accredited to HP Govt./Editor of Himachal Editions of HT, Times of India, Tribune, Indian Express, Amar Ujala, Dainik Bhaskar, Punjab Kesari, Dainik Jagran, Vir Pratap & Divya Himachal on official visits invited by UHF.",
        shortName: "Press Correspondents (Official / Invited by UHF)",
        vvip: 2500,
        vip: 1500,
        ordinary: 1000,
        type: "official"
    },
    {
        id: "cat_8_press_private",
        srNo: "8(ii)",
        title: "Press Correspondents & Media Personnel (On Private Visit)",
        description: "Accredited journalists and media personnel on personal or private visit.",
        shortName: "Press Correspondents (Private Visit)",
        vvip: 3000,
        vip: 2000,
        ordinary: 1500,
        type: "private"
    },
    {
        id: "cat_9_private_person",
        srNo: "9",
        title: "Private Person / Individual Visitors",
        description: "Private person/individual visitors and general public.",
        shortName: "Private Person / Individual Visitor",
        vvip: 3000,
        vip: 2000,
        ordinary: 1500,
        type: "private"
    },
    {
        id: "cat_10_dorm_official",
        srNo: "10(i)",
        title: "Dormitory (On Official Visit)",
        description: "Dormitory bed per night for individuals on official visit.",
        shortName: "Dormitory Bed (Official Visit)",
        vvip: 0,
        vip: 0,
        ordinary: 100,
        type: "dormitory"
    },
    {
        id: "cat_10_dorm_private",
        srNo: "10(ii)",
        title: "Dormitory (On Private Visit)",
        description: "Dormitory bed per night for individuals on private visit.",
        shortName: "Dormitory Bed (Private Visit)",
        vvip: 0,
        vip: 0,
        ordinary: 300,
        type: "dormitory"
    },
    {
        id: "cat_11_wash_change",
        srNo: "11",
        title: "Wash / Change (Short Transit Stay)",
        description: "Short stay freshen-up: 50% of the respective room rent for VVIP, VIP, or Ordinary sets.",
        shortName: "Wash & Change (Short Stay - 50% Rent)",
        isPercentage: true,
        percentage: 50,
        vvip: 1250,
        vip: 750,
        ordinary: 500,
        type: "short_stay"
    }
];

const DEFAULT_DINING_HALL_RATES = [
    {
        id: "dining_1_functions",
        srNo: "1",
        description: "Use of Dining Hall of Guest House by University employees/residents of the campus for birthday and other functions",
        rate: 1000,
        unit: "per day",
        category: "Campus Residents (Functions)"
    },
    {
        id: "dining_2_marriages",
        srNo: "2",
        description: "Use of Dining Hall of Guest House by University employees/residents of the campus for marriages",
        rate: 4000,
        unit: "per day",
        category: "Campus Residents (Marriages)"
    },
    {
        id: "dining_3_outside",
        srNo: "3",
        description: "Use of Dining Hall of Guest House by outside agencies for marriages etc.",
        rate: 10000,
        unit: "per day",
        category: "Outside Agencies (Marriages)"
    }
];

// Comprehensive Statutory Catering & Dining Charges Catalog
// Ref 1: Notification No. UHF/EO-55/2026/- 2125-2160, Dated: 10/09/2026
// Ref 2: Notification No. UHF/EO-55/2026/- 2511-56, Dated: 23/10/2026
const DEFAULT_STATUTORY_DINING_CATALOG = [
    // --- 1. BREAKFAST (Notification 2125-2160) ---
    {
        id: "bf_ordinary",
        section: "breakfast",
        sectionTitle: "Breakfast (Notification 2125-2160)",
        srNo: "1(a)",
        title: "Breakfast - Ordinary Menu",
        description: "Prantha (Stuffed-2 No. + Dahi or Butter + Achar or Sauce) OR Bread Slice-4 Pc. + Butter or Jam + Milk one glass OR Bread Slice-4 Pc. + Omelet-2 eggs + Tea one cup",
        rate: 100,
        unit: "per person",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },
    {
        id: "bf_richer",
        section: "breakfast",
        sectionTitle: "Breakfast (Notification 2125-2160)",
        srNo: "1(b)",
        title: "Breakfast - Richer Menu (on order)",
        description: "Bread slice-4 Pc + Butter or Jam + Milk + Corn Flakes",
        rate: 100,
        unit: "per person",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },
    {
        id: "bf_special",
        section: "breakfast",
        sectionTitle: "Breakfast (Notification 2125-2160)",
        srNo: "1(c)",
        title: "Breakfast - Special Menu (on order)",
        description: "Special Menu (Ordinary or Richer Menu) + Seasonal Fruits",
        rate: 150,
        unit: "per person",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },

    // --- 2. LUNCH / DINNER (VEG) (Notification 2125-2160) ---
    {
        id: "veg_ordinary",
        section: "lunch_dinner_veg",
        sectionTitle: "Lunch / Dinner (Veg) (Notification 2125-2160)",
        srNo: "2(a)",
        title: "Lunch / Dinner (Veg) - Ordinary Menu",
        description: "Any Dal or Rajmash or Channa + Seasonal Veg. or Mix. Veg. + Salad + Chapati + Rice (Basmati)",
        rate: 110,
        unit: "per person",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },
    {
        id: "veg_richer",
        section: "lunch_dinner_veg",
        sectionTitle: "Lunch / Dinner (Veg) (Notification 2125-2160)",
        srNo: "2(b)",
        title: "Lunch / Dinner (Veg) - Richer Menu",
        description: "Mutter Paneer or Mutter Mushroom or Dal Makhni or Pakora Kari or Mushroom Masala + Any Dal or Rajmash or Channa + Seasonal Veg. or Mix. Veg. + Salad + Chapati + Rice (Basmati)",
        rate: 150,
        unit: "per person",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },
    {
        id: "veg_special",
        section: "lunch_dinner_veg",
        sectionTitle: "Lunch / Dinner (Veg) (Notification 2125-2160)",
        srNo: "2(c)",
        title: "Lunch / Dinner (Veg) - Special Menu",
        description: "Soup + Shahi Paneer or Malai Kofta + Any Dal or Rajmash or Channa + Seasonal Veg. or Mix. Veg. + Raita + Salad + Chapati + Rice (Basmati) + Papad + Sweet Dish (Kheer or Gulab Jamun 1 Pc. or Sevian or Custard) or Ice Cream",
        rate: 200,
        unit: "per person",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },

    // --- 3. LUNCH / DINNER (NON-VEG) (Notification 2125-2160) ---
    {
        id: "nonveg_mutton_ordinary",
        section: "lunch_dinner_nonveg",
        sectionTitle: "Lunch / Dinner (Non-Veg) (Notification 2125-2160)",
        srNo: "3(a)",
        title: "Lunch / Dinner (Non-Veg) - Mutton Ordinary",
        description: "Mutton + Dal or Sabji + Salad + Rice (Basmati) + Chapati",
        rate: 350,
        unit: "per person",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },
    {
        id: "nonveg_chicken_ordinary",
        section: "lunch_dinner_nonveg",
        sectionTitle: "Lunch / Dinner (Non-Veg) (Notification 2125-2160)",
        srNo: "3(b)",
        title: "Lunch / Dinner (Non-Veg) - Chicken Ordinary",
        description: "Chicken + Dal or Sabji + Salad + Rice (Basmati) + Chapati",
        rate: 300,
        unit: "per person",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },
    {
        id: "nonveg_mutton_special",
        section: "lunch_dinner_nonveg",
        sectionTitle: "Lunch / Dinner (Non-Veg) (Notification 2125-2160)",
        srNo: "3(c)",
        title: "Lunch / Dinner (Non-Veg) - Mutton Special",
        description: "Chicken soup + Mutton + Any Dal or Rajmash or Channa + Seasonal Veg. or Mix. Veg. + Raita + Salad + Chapati + Rice (Basmati) + Papad + Sweet Dish (Kheer or Gulab Jamun 1 Pc or Sevian or Custard)",
        rate: 500,
        unit: "per person",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },
    {
        id: "nonveg_chicken_special",
        section: "lunch_dinner_nonveg",
        sectionTitle: "Lunch / Dinner (Non-Veg) (Notification 2125-2160)",
        srNo: "3(d)",
        title: "Lunch / Dinner (Non-Veg) - Chicken Special",
        description: "Chicken soup + Chicken + Any Dal or Rajmash or Channa + Seasonal Veg. or Mix. Veg. + Raita + Salad + Chapati + Rice (Basmati) + Papad + Sweet Dish (Kheer or Gulab Jamun 1 Pc or Sevian or Custard)",
        rate: 400,
        unit: "per person",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },

    // --- 4. LUNCH / DINNER (SIMPLE) ---
    {
        id: "meal_simple",
        section: "lunch_dinner_simple",
        sectionTitle: "Simple Meal (Notification 2125-2160)",
        srNo: "4",
        title: "Lunch / Dinner (Simple)",
        description: "Rice (Basmati) + Dal OR Chapati + Dal",
        rate: 70,
        unit: "per person",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },

    // --- 5. EXTRA ITEMS & BEVERAGES ---
    {
        id: "extra_tea",
        section: "extra_items",
        sectionTitle: "Extra Items & Beverages (Notification 2125-2160)",
        srNo: "5(a)",
        title: "Tea",
        description: "Freshly prepared tea",
        rate: 20,
        unit: "per cup",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },
    {
        id: "extra_milk",
        section: "extra_items",
        sectionTitle: "Extra Items & Beverages (Notification 2125-2160)",
        srNo: "5(b)",
        title: "Milk with Sugar (200 ml)",
        description: "Milk with sugar (200 ml)",
        rate: 30,
        unit: "per cup (200 ml)",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },
    {
        id: "extra_soup",
        section: "extra_items",
        sectionTitle: "Extra Items & Beverages (Notification 2125-2160)",
        srNo: "5(c)",
        title: "Veg. Soup",
        description: "Hot vegetable soup",
        rate: 40,
        unit: "per cup",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },
    {
        id: "extra_coffee",
        section: "extra_items",
        sectionTitle: "Extra Items & Beverages (Notification 2125-2160)",
        srNo: "5(d)",
        title: "Coffee",
        description: "Freshly brewed coffee",
        rate: 50,
        unit: "per cup",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },
    {
        id: "extra_dalia",
        section: "extra_items",
        sectionTitle: "Extra Items & Beverages (Notification 2125-2160)",
        srNo: "5(e)",
        title: "Dalia or Milk + Cornflakes",
        description: "Dalia or Milk with Cornflakes",
        rate: 60,
        unit: "per bowl",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },
    {
        id: "extra_dahi",
        section: "extra_items",
        sectionTitle: "Extra Items & Beverages (Notification 2125-2160)",
        srNo: "5(f)",
        title: "Dahi (150 gm)",
        description: "Fresh plain curd / dahi (150 gm)",
        rate: 40,
        unit: "per plate (150 gm)",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },
    {
        id: "extra_raita",
        section: "extra_items",
        sectionTitle: "Extra Items & Beverages (Notification 2125-2160)",
        srNo: "5(g)",
        title: "Boondi or Ghia Raita (150 gm)",
        description: "Boondi or Ghia Raita (150 gm)",
        rate: 40,
        unit: "per plate (150 gm)",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },
    {
        id: "extra_sweets",
        section: "extra_items",
        sectionTitle: "Extra Items & Beverages (Notification 2125-2160)",
        srNo: "5(h)",
        title: "Ice cream, Rasgula, Gulabjamun, Rasmalai etc.",
        description: "Ice cream or Indian sweets (Market rate plus Rs. 15 service charge)",
        rate: 50,
        unit: "per serving",
        notification: "UHF/EO-55/2026/- 2125-2160"
    },

    // --- 6. COMBO PACKS (Notification 2511-56) ---
    {
        id: "combo_standard",
        section: "combo_packs",
        sectionTitle: "Combo Packs (Notification 2511-56)",
        srNo: "CP-1",
        title: "VEG COMBO (STANDARD)",
        description: "Dal Makhani/Rajma Masala/Dal Fry + Matar Paneer/Veg Kofta + Aloo Gobhi/Aloo Matar/Aloo Bean + Kadi Pakoda + Plain Rice + Tawa Chapati + Salad + Kheer/Sevian/Gulab Jamun/Ice Cream + Soup + Raita",
        rate: 350,
        unit: "per pack",
        notification: "UHF/EO-55/2026/- 2511-56"
    },
    {
        id: "combo_super",
        section: "combo_packs",
        sectionTitle: "Combo Packs (Notification 2511-56)",
        srNo: "CP-2",
        title: "VEG COMBO (SUPER)",
        description: "Shahi Paneer/Malai Kofta + Bhindi Masala/Chana Masala/Mushroom Masala/Mix Veg + Rajma Masala/Dal Makhani + Kadi Pakoda + Plain Rice + Tawa Chapati + Salad + Gajar Ka Halwa/Moong Dal Halwa + Soup + Fruit Custard + Fruit Raita",
        rate: 450,
        unit: "per pack",
        notification: "UHF/EO-55/2026/- 2511-56"
    },

    // --- 7. MAIN COURSE (VEG) (Notification 2511-56) ---
    { id: "mc_veg_dal_makhani", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-1", title: "Dal Makhani", description: "Slow-cooked black lentils with butter and cream", rate: 150, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_veg_rajma", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-2", title: "Rajma Masala", description: "Himalayan red kidney beans in rich spiced gravy", rate: 150, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_veg_dal_fry", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-3", title: "Dal Fry", description: "Yellow dal tempered with cumin, garlic and ghee", rate: 120, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_veg_matar_paneer", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-4", title: "Matar Paneer", description: "Fresh cottage cheese and green peas in rich gravy", rate: 160, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_veg_shahi_paneer", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-5", title: "Shahi Paneer", description: "Royal cottage cheese in aromatic creamy cashew-nut gravy", rate: 190, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_veg_mix_veg", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-6", title: "Mix Veg", description: "Assorted seasonal garden vegetables sauteed in mild spices", rate: 150, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_veg_mushroom", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-7", title: "Mushroom Masala", description: "Fresh button mushrooms cooked in rich masala gravy", rate: 150, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_veg_aloo_gobhi", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-8", title: "Aloo Gobhi", description: "Potatoes and cauliflower florets with ginger and coriander", rate: 100, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_veg_aloo_matar", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-9", title: "Aloo Matar", description: "Homestyle potatoes and fresh peas", rate: 100, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_veg_aloo_bean", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-10", title: "Aloo Bean", description: "French beans and potatoes tossed in spices", rate: 100, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_veg_chana_masala", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-11", title: "Chana Masala", description: "Spiced chickpeas in tangy tomato-onion gravy", rate: 150, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_veg_malai_kofta", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-12", title: "Malai Kofta", description: "Paneer dumplings in velvety cashew cream gravy", rate: 200, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_veg_kadi_pakoda", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-13", title: "Kadi Pakoda", description: "Traditional gram flour fritters in seasoned yogurt curry", rate: 150, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_veg_bhindi_masala", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-14", title: "Bhindi Masala", description: "Crisp okra cooked with sliced onions and spices", rate: 160, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_veg_veg_kofta", section: "main_course_veg", sectionTitle: "Main Course (Veg) (Notification 2511-56)", srNo: "MCV-15", title: "Veg Kofta", description: "Mixed vegetable dumplings in spiced curry", rate: 150, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },

    // --- 8. MAIN COURSE (NON-VEG) (Notification 2511-56) ---
    { id: "mc_nonveg_chicken_curry", section: "main_course_nonveg", sectionTitle: "Main Course (Non-Veg) (Notification 2511-56)", srNo: "MCNV-1", title: "Chicken Curry", description: "Tender chicken pieces in traditional gravy", rate: 300, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_nonveg_mutton_chicken", section: "main_course_nonveg", sectionTitle: "Main Course (Non-Veg) (Notification 2511-56)", srNo: "MCNV-2", title: "Mutton Chicken", description: "Dual-meat specialty preparation", rate: 400, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_nonveg_egg_curry", section: "main_course_nonveg", sectionTitle: "Main Course (Non-Veg) (Notification 2511-56)", srNo: "MCNV-3", title: "Egg Curry", description: "Boiled eggs in spiced tomato-onion curry", rate: 150, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_nonveg_fish_curry", section: "main_course_nonveg", sectionTitle: "Main Course (Non-Veg) (Notification 2511-56)", srNo: "MCNV-4", title: "Fish Curry", description: "Fresh fish fillets cooked in rich curry sauce", rate: 400, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_nonveg_mutton_curry", section: "main_course_nonveg", sectionTitle: "Main Course (Non-Veg) (Notification 2511-56)", srNo: "MCNV-5", title: "Mutton Curry", description: "Slow-simmered tender mutton in authentic spices", rate: 500, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "mc_nonveg_rara_chicken", section: "main_course_nonveg", sectionTitle: "Main Course (Non-Veg) (Notification 2511-56)", srNo: "MCNV-6", title: "Rara Chicken", description: "Succulent chicken cooked in spiced minced gravy", rate: 150, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },

    // --- 9. RAITA & SALAD (Notification 2511-56) ---
    { id: "rs_boondi_raita", section: "raita_salad", sectionTitle: "Raita & Salad (Notification 2511-56)", srNo: "RS-1", title: "Boondi Raita", description: "Crisp gram flour pearls in spiced yogurt", rate: 80, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "rs_mix_raita", section: "raita_salad", sectionTitle: "Raita & Salad (Notification 2511-56)", srNo: "RS-2", title: "Mix Raita", description: "Yogurt with diced vegetables and roasted cumin", rate: 80, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "rs_cucumber_raita", section: "raita_salad", sectionTitle: "Raita & Salad (Notification 2511-56)", srNo: "RS-3", title: "Cucumber Raita", description: "Grated cucumber in chilled seasoned yogurt", rate: 80, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "rs_plain_curd", section: "raita_salad", sectionTitle: "Raita & Salad (Notification 2511-56)", srNo: "RS-4", title: "Plain Curd", description: "Fresh wholesome curd", rate: 50, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "rs_green_salad", section: "raita_salad", sectionTitle: "Raita & Salad (Notification 2511-56)", srNo: "RS-5", title: "Green Salad", description: "Sliced cucumber, tomatoes, onions, carrots and green chilies", rate: 80, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "rs_cucumber_mix_salad", section: "raita_salad", sectionTitle: "Raita & Salad (Notification 2511-56)", srNo: "RS-6", title: "Cucumber and Mix Veg Salad", description: "Crisp cucumber with mixed garden vegetables and lemon dressing", rate: 100, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },

    // --- 10. RICE & PULLAO (Notification 2511-56) ---
    { id: "rp_plain_rice", section: "rice_pullao", sectionTitle: "Rice & Pullao (Notification 2511-56)", srNo: "RP-1", title: "Plain Rice", description: "Steamed long grain Basmati rice", rate: 100, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "rp_veg_pullao", section: "rice_pullao", sectionTitle: "Rice & Pullao (Notification 2511-56)", srNo: "RP-2", title: "Veg Pullao", description: "Fragrant basmati rice cooked with garden vegetables", rate: 120, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "rp_fried_rice", section: "rice_pullao", sectionTitle: "Rice & Pullao (Notification 2511-56)", srNo: "RP-3", title: "Fried Rice", description: "Wok-tossed basmati rice with vegetables and light soy", rate: 130, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "rp_veg_biryani", section: "rice_pullao", sectionTitle: "Rice & Pullao (Notification 2511-56)", srNo: "RP-4", title: "Veg Briyani", description: "Layered spiced basmati rice and vegetables", rate: 250, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "rp_chicken_biryani", section: "rice_pullao", sectionTitle: "Rice & Pullao (Notification 2511-56)", srNo: "RP-5", title: "Chicken Briyani", description: "Aromatic dum biryani with tender chicken pieces", rate: 350, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "rp_egg_fried_rice", section: "rice_pullao", sectionTitle: "Rice & Pullao (Notification 2511-56)", srNo: "RP-6", title: "Egg Fried Rice", description: "Fried rice scrambled with farm fresh eggs", rate: 180, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "rp_chicken_fried_rice", section: "rice_pullao", sectionTitle: "Rice & Pullao (Notification 2511-56)", srNo: "RP-7", title: "Chicken Fried Rice", description: "Fried rice with shredded chicken and scallions", rate: 200, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },

    // --- 11. ROTI (Notification 2511-56) ---
    { id: "roti_tawa", section: "roti", sectionTitle: "Roti (Notification 2511-56)", srNo: "R-1", title: "Tawa Roti", description: "Whole wheat freshly baked flatbread", rate: 15, unit: "per piece", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "roti_naan", section: "roti", sectionTitle: "Roti (Notification 2511-56)", srNo: "R-2", title: "Naan", description: "Soft leavened refined flour bread", rate: 40, unit: "per piece", notification: "UHF/EO-55/2026/- 2511-56" },

    // --- 12. SWEET DISH (Notification 2511-56) ---
    { id: "sweet_gulab_jamun", section: "sweet_dish", sectionTitle: "Sweet Dish (Notification 2511-56)", srNo: "SD-1", title: "Gulab Jamun (2 Nos.)", description: "Warm khoya dumplings in cardamom saffron syrup", rate: 50, unit: "per plate (2 pcs)", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "sweet_kheer", section: "sweet_dish", sectionTitle: "Sweet Dish (Notification 2511-56)", srNo: "SD-2", title: "Kheer", description: "Creamy traditional rice pudding with nuts", rate: 50, unit: "per bowl", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "sweet_sevian", section: "sweet_dish", sectionTitle: "Sweet Dish (Notification 2511-56)", srNo: "SD-3", title: "Sevian", description: "Sweet vermicelli in reduced milk", rate: 50, unit: "per bowl", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "sweet_ice_cream", section: "sweet_dish", sectionTitle: "Sweet Dish (Notification 2511-56)", srNo: "SD-4", title: "Ice Cream", description: "Assorted flavours", rate: 50, unit: "per cup", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "sweet_gajar_halwa", section: "sweet_dish", sectionTitle: "Sweet Dish (Notification 2511-56)", srNo: "SD-5", title: "Gajar Ka Halwa", description: "Winter carrot pudding slow cooked in milk, khoya and ghee", rate: 80, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },
    { id: "sweet_moong_dal_halwa", section: "sweet_dish", sectionTitle: "Sweet Dish (Notification 2511-56)", srNo: "SD-6", title: "Moong Dal Halwa", description: "Rich yellow lentil halwa prepared in pure desi ghee", rate: 100, unit: "per plate", notification: "UHF/EO-55/2026/- 2511-56" },

    // --- 13. LOUNGE & DINING HALL BOOKING (Notification 1615-40) ---
    {
        id: "dining_1_functions",
        section: "hall_booking",
        sectionTitle: "Lounge & Dining Hall Booking (Notification 1615-40)",
        srNo: "DH-1",
        title: "Dining Hall - Birthday / Functions (Campus Residents)",
        description: "Use of Dining Hall of Guest House by University employees/residents of the campus for birthday and other functions",
        rate: 1000,
        unit: "per day",
        notification: "UHF/EO-55/2025/- 1615-40"
    },
    {
        id: "dining_2_marriages",
        section: "hall_booking",
        sectionTitle: "Lounge & Dining Hall Booking (Notification 1615-40)",
        srNo: "DH-2",
        title: "Dining Hall - Marriages (Campus Residents)",
        description: "Use of Dining Hall of Guest House by University employees/residents of the campus for marriages",
        rate: 4000,
        unit: "per day",
        notification: "UHF/EO-55/2025/- 1615-40"
    },
    {
        id: "dining_3_outside",
        section: "hall_booking",
        sectionTitle: "Lounge & Dining Hall Booking (Notification 1615-40)",
        srNo: "DH-3",
        title: "Dining Hall - Outside Agencies (Marriages / Functions)",
        description: "Use of Dining Hall of Guest House by outside agencies for marriages etc.",
        rate: 10000,
        unit: "per day",
        notification: "UHF/EO-55/2025/- 1615-40"
    }
];

// Legacy stub for backward compatibility
const DEFAULT_DEE_CATEGORIES = [];

// Helper functions to get/set with localStorage persistence
class EstateTariffManager {
    static getNotificationMeta() {
        const stored = localStorage.getItem('uhf_estate_meta');
        if (stored) {
            try { return JSON.parse(stored); } catch (e) { }
        }
        return { ...DEFAULT_ESTATE_NOTIFICATION };
    }

    static saveNotificationMeta(meta) {
        localStorage.setItem('uhf_estate_meta', JSON.stringify(meta));
    }

    static getEstateCategories() {
        const stored = localStorage.getItem('uhf_estate_categories');
        if (stored) {
            try { return JSON.parse(stored); } catch (e) { }
        }
        return JSON.parse(JSON.stringify(DEFAULT_ESTATE_CATEGORIES));
    }

    static saveEstateCategories(categories) {
        localStorage.setItem('uhf_estate_categories', JSON.stringify(categories));
    }

    static getDiningCatalog() {
        const stored = localStorage.getItem('uhf_dining_catalog_rates');
        if (stored) {
            try {
                const parsed = JSON.parse(stored);
                if (Array.isArray(parsed) && parsed.length > 0) return parsed;
            } catch (e) { }
        }
        return JSON.parse(JSON.stringify(DEFAULT_STATUTORY_DINING_CATALOG));
    }

    static saveDiningCatalog(catalog) {
        localStorage.setItem('uhf_dining_catalog_rates', JSON.stringify(catalog));
    }

    static resetDiningCatalog() {
        localStorage.removeItem('uhf_dining_catalog_rates');
    }

    static getDiningHallRates() {
        const catalog = this.getDiningCatalog();
        const hallRates = catalog.filter(c => c.section === 'hall_booking');
        if (hallRates.length > 0) return hallRates;
        const stored = localStorage.getItem('uhf_dining_hall_rates');
        if (stored) {
            try { return JSON.parse(stored); } catch (e) { }
        }
        return JSON.parse(JSON.stringify(DEFAULT_DINING_HALL_RATES));
    }

    static saveDiningHallRates(rates) {
        localStorage.setItem('uhf_dining_hall_rates', JSON.stringify(rates));
        const catalog = this.getDiningCatalog();
        rates.forEach(r => {
            const item = catalog.find(c => c.id === r.id);
            if (item) {
                item.rate = Number(r.rate) || item.rate;
                item.description = r.description || item.description;
            }
        });
        this.saveDiningCatalog(catalog);
    }

    static getDeeCategories() {
        const stored = localStorage.getItem('uhf_dee_categories');
        if (stored) {
            try { return JSON.parse(stored); } catch (e) { }
        }
        return JSON.parse(JSON.stringify(DEFAULT_DEE_CATEGORIES));
    }

    static saveDeeCategories(categories) {
        localStorage.setItem('uhf_dee_categories', JSON.stringify(categories));
    }

    static resetToDefaults() {
        localStorage.removeItem('uhf_estate_meta');
        localStorage.removeItem('uhf_estate_categories');
        localStorage.removeItem('uhf_dining_hall_rates');
        localStorage.removeItem('uhf_dining_catalog_rates');
        localStorage.removeItem('uhf_dee_categories');
    }

    static calculateStayRate(wing, categoryId, setType) {
        const categories = this.getEstateCategories();
        const cat = categories.find(c => c.id === categoryId) || categories[0];
        if (!cat) return 1000;

        if (cat.id.startsWith('cat_10_dorm')) {
            return cat.ordinary;
        }

        if (cat.isPercentage) {
            if (setType === 'vvip') return Math.round(2500 * (cat.percentage / 100));
            if (setType === 'vip') return Math.round(1500 * (cat.percentage / 100));
            return Math.round(1000 * (cat.percentage / 100));
        }

        if (setType === 'vvip') return cat.vvip;
        if (setType === 'vip') return cat.vip;
        return cat.ordinary;
    }

    static getCategoryById(id) {
        const estate = this.getEstateCategories().find(c => c.id === id);
        if (estate) {
            return Object.assign({}, estate, {
                vipRate: estate.vip,
                vvipRate: estate.vvip,
                ordinaryRate: estate.ordinary
            });
        }
        return null;
    }

    static calculateTariff(categoryId, setType, nights = 1) {
        const rate = this.calculateStayRate('estate', categoryId, setType);
        let surcharge = 0;
        let hasSurcharge = false;
        if (nights > 7) {
            hasSurcharge = true;
            surcharge = Math.round(rate * 0.5 * (nights - 7));
        }
        const total = (rate * nights) + surcharge;
        return {
            baseNightlyRate: rate,
            nights: nights,
            hasSurcharge: hasSurcharge,
            surchargeAmount: surcharge,
            total: total
        };
    }
}

// Attach to window object for global browser access
if (typeof window !== 'undefined') {
    window.EstateTariffManager = EstateTariffManager;
    window.DEFAULT_ESTATE_CATEGORIES = DEFAULT_ESTATE_CATEGORIES;
    window.DEFAULT_DINING_HALL_RATES = DEFAULT_DINING_HALL_RATES;
    window.DEFAULT_STATUTORY_DINING_CATALOG = DEFAULT_STATUTORY_DINING_CATALOG;
    window.DEFAULT_DEE_CATEGORIES = DEFAULT_DEE_CATEGORIES;
}
