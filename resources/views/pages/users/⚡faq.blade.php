<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new class () extends Component {
    // This is ready for future use.
    // If you ever move FAQs to the database, you can load them here.
};
?>

<div>
    {{-- Main FAQ UI Container --}}
    <div class="max-w-4xl mx-auto p-6 my-8 bg-white rounded-2xl shadow-sm border border-gray-100">
        <h1 class="text-3xl font-black text-gray-900 mb-8 text-center">সচরাচর জিজ্ঞাসিত প্রশ্নাবলী (FAQ)</h1>
        
        <div class="space-y-6">
            {{-- FAQ Item 1 --}}
            <div class="faq-item border-b border-gray-100 pb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2">আমি কীভাবে অনলাইনে পণ্য অর্ডার করব?</h3>
                <p class="text-gray-600 leading-relaxed">
                    আপনি যে পণ্যটি কিনতে চান তা নির্বাচন করে 'Order Now' বা 'Add to Cart' বাটনে ক্লিক করুন। এরপর আপনার নাম, সঠিক ডেলিভারি ঠিকানা এবং মোবাইল নম্বর প্রদান করে অর্ডারটি সম্পন্ন করুন।
                </p>
            </div>

            {{-- FAQ Item 2 --}}
            <div class="faq-item border-b border-gray-100 pb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2">ডেলিভারি চার্জ কত এবং পণ্য পেতে কতদিন সময় লাগে?</h3>
                <p class="text-gray-600 leading-relaxed">
                    আমাদের সাধারণ ডেলিভারি চার্জ ৬০ টাকা। অর্ডার কনফার্ম হওয়ার পর আপনার ঠিকানায় পণ্য পৌঁছাতে সাধারণত ২ থেকে ৩ কার্যদিবস সময় লাগে।
                </p>
            </div>

            {{-- FAQ Item 3 --}}
            <div class="faq-item pb-2">
                <h3 class="text-xl font-bold text-gray-800 mb-2">পণ্য পরিবর্তন বা ফেরত দেওয়ার সুযোগ আছে কি?</h3>
                <p class="text-gray-600 leading-relaxed">
                    হ্যাঁ, পণ্য হাতে পাওয়ার পর যদি কোনো ত্রুটি থাকে, তবে আপনি ডেলিভারি ম্যান থাকা অবস্থায় আমাদের সাথে যোগাযোগ করে পণ্যটি ফেরত বা পরিবর্তন করতে পারবেন।
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Note: Change 'schema' to whatever name you gave your @stack in the layout file's <head> --}}
@push('faqSchema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "আমি কীভাবে অনলাইনে পণ্য অর্ডার করব?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "আপনি যে পণ্যটি কিনতে চান তা নির্বাচন করে 'Order Now' বা 'Add to Cart' বাটনে ক্লিক করুন। এরপর আপনার নাম, সঠিক ডেলিভারি ঠিকানা এবং মোবাইল নম্বর প্রদান করে অর্ডারটি সম্পন্ন করুন।"
      }
    },
    {
      "@type": "Question",
      "name": "ডেলিভারি চার্জ কত এবং পণ্য পেতে কতদিন সময় লাগে?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "আমাদের সাধারণ ডেলিভারি চার্জ ৬০ টাকা। অর্ডার কনফার্ম হওয়ার পর আপনার ঠিকানায় পণ্য পৌঁছাতে সাধারণত ২ থেকে ৩ কার্যদিবস সময় লাগে।"
      }
    },
    {
      "@type": "Question",
      "name": "পণ্য পরিবর্তন বা ফেরত দেওয়ার সুযোগ আছে কি?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "হ্যাঁ, পণ্য হাতে পাওয়ার পর যদি কোনো ত্রুটি থাকে, তবে আপনি ডেলিভারি ম্যান থাকা অবস্থায় আমাদের সাথে যোগাযোগ করে পণ্যটি ফেরত বা পরিবর্তন করতে পারবেন।"
      }
    }
  ]
}
</script>
@endpush