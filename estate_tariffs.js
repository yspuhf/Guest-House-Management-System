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

    static getDiningHallRates() {
        const stored = localStorage.getItem('uhf_dining_hall_rates');
        if (stored) {
            try { return JSON.parse(stored); } catch (e) { }
        }
        return JSON.parse(JSON.stringify(DEFAULT_DINING_HALL_RATES));
    }

    static saveDiningHallRates(rates) {
        localStorage.setItem('uhf_dining_hall_rates', JSON.stringify(rates));
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
    window.DEFAULT_DEE_CATEGORIES = DEFAULT_DEE_CATEGORIES;
}
