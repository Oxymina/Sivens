<template>
  <section>
    <v-row no-gutters>
      <v-col cols="12">
        <SectionsHeroAlt :hero-alt="heroAlt" />
        <v-container>
          <v-row class="py-16">
            <v-col cols="12">
              <h2
                class="text-h4 text-md-h3 text-center font-weight-black text-capitalize"
              >
                Who we are?
              </h2>
            </v-col>
            <v-col cols="12" md="6">
              <p>
                Welcome to Sivēns.lv, Latvia's premier restaurant rating company
                dedicated to showcasing the finest culinary experiences our
                beautiful country has to offer. At Sivēns.lv, we are passionate
                about celebrating the rich and diverse gastronomic landscape of
                Latvia, from the heart of Riga to the hidden gems in our
                picturesque countryside.
              </p>
              <blockquote class="text-h4 pa-5 my-4 font-weight-thin">
                What is our mission?
              </blockquote>
              <p>
                Our mission is to provide an authoritative and trusted guide for
                food enthusiasts, locals, and tourists alike, helping them
                discover the best dining establishments across Latvia. We pride
                ourselves on our rigorous and impartial evaluation process,
                which ensures that only the most exceptional restaurants receive
                our coveted ratings.
              </p>
            </v-col>
            <v-col cols="12" md="6">
              <p>
                While we draw inspiration from renowned global rating systems,
                Sivēns.lv is uniquely focused on the Latvian culinary scene. Our
                team of expert reviewers, who possess an in-depth knowledge of
                local and international cuisine, visit each restaurant
                anonymously to ensure an authentic and unbiased dining
                experience. <br /><br />At Sivēns.lv, we believe that dining is
                more than just food; it’s about the ambiance, service, and the
                story each restaurant tells. Our ratings reflect the complete
                dining experience, giving you a comprehensive insight into what
                makes each establishment special.<br /><br />
                Join us on a culinary journey through Latvia, and let Sivēns.lv
                be your trusted guide to the finest dining experiences our
                country has to offer. Whether you're seeking a cozy bistro, a
                fine dining restaurant, or a hidden gem off the beaten path, we
                are here to help you make the most informed dining choices.<br />
                <br />
                Discover excellence. Discover Sivēns.lv.
              </p>
            </v-col>
          </v-row>
        </v-container>
        <SectionsTeam :our-team="ourTeam" />

        <v-row no-gutters>
          <v-col cols="12" md="6" align-self="center">
            <v-img
              max-height="800"
              src="Negative-Restaurant-Review-Staff.png"
              lazy-src="Negative-Restaurant-Review-Staff.png"
            >
            </v-img>
          </v-col>
          <v-col cols="12" md="6" align-self="center">
            <div class="pa-lg-16 pa-md-10 px-4 py-16">
              <h2 class="text-h3 text-center font-weight-black">Our Vision</h2>
              <br />
              <p>
                At Sivēns.lv, our vision is to become the definitive guide for
                culinary excellence in Latvia, setting the highest standards in
                restaurant rating and dining experiences. We aspire to inspire
                both locals and visitors to explore and appreciate the rich
                tapestry of flavors and traditions that define Latvian cuisine.
                <br /><br />We aim to foster a community where food enthusiasts,
                chefs, and restaurateurs can connect, share, and celebrate their
                passion for exceptional dining. By highlighting the best that
                Latvia has to offer, we seek to elevate the profile of our
                nation’s culinary scene on the global stage. <br /><br />Our
                commitment is to integrity, transparency, and excellence in
                every review we publish. We envision a future where Sivēns.lv is
                synonymous with trust and quality, where our ratings are seen as
                a mark of distinction and a reliable guide for all who seek
                remarkable dining experiences. <br /><br />Through our
                dedication, we hope to not only guide diners but also encourage
                continuous improvement and innovation within the Latvian
                restaurant industry. We believe that by shining a spotlight on
                outstanding establishments, we can inspire others to reach new
                heights of culinary artistry. <br /><br />Together, with the
                support of our community, we strive to celebrate and preserve
                the unique culinary heritage of Latvia while embracing the
                future of gastronomy. This is the vision of Sivēns.lv: to be the
                beacon of excellence in Latvian dining, guiding you to
                unforgettable culinary adventures.
              </p>
            </div>
          </v-col>
        </v-row>
        <v-row no-gutters class="flex-row-reverse">
          <v-col cols="12" md="6" align-self="center">
            <v-img
              max-height="800"
              src="peer-review-Edanz-1024x686.jpg"
              lazy-src="peer-review-Edanz-1024x686.jpg"
            >
            </v-img>
          </v-col>
          <v-col cols="12" md="6" align-self="center">
            <div class="pa-lg-16 pa-md-10 px-4 py-16">
              <h2 class="text-h3 text-center font-weight-black">Our Mission</h2>
              <br />
              <p>
                At Sivēns.lv, our mission is to passionately champion the
                diverse and vibrant culinary landscape of Latvia by providing
                discerning diners with reliable, insightful, and unbiased
                restaurant ratings. We are dedicated to empowering individuals
                to make informed dining choices and to discover the true essence
                of Latvian gastronomy. <br /><br />Through our meticulous
                evaluation process and commitment to transparency, we strive to
                uphold the highest standards of integrity and excellence in our
                ratings. Our mission is to be the trusted authority that guides
                both locals and visitors alike to the most exceptional dining
                experiences across Latvia. <br /><br />We endeavor to celebrate
                and promote the talents of Latvian chefs and restaurateurs,
                recognizing their dedication to culinary innovation,
                authenticity, and hospitality. By showcasing the best that
                Latvia has to offer, we aim to foster a deeper appreciation for
                our nation's culinary heritage and support the growth of our
                local food community. <br /><br />Furthermore, we are committed
                to continuously improving and evolving our platform to better
                serve our users and the restaurant industry. Our mission is to
                foster a dynamic and inclusive food culture where everyone can
                explore, appreciate, and celebrate the rich tapestry of flavors
                that define Latvian cuisine. <br /><br />At Sivēns.lv, we
                believe that great food has the power to bring people together,
                spark conversations, and create lasting memories. Our mission is
                to be your trusted companion on your culinary journey through
                Latvia, guiding you to unforgettable dining experiences that
                tantalize the senses and nourish the soul.
              </p>
            </div>
          </v-col>
        </v-row>

        <SectionsTimeline />
      </v-col>
    </v-row>
  </section>
</template>

<script>
export default {
  data() {
    return {
      heroAlt: [
        {
          src: 'pexels-moose-photos-1036641.jpg',
          heading: ' About Us ',
        },
      ],
      ourTeam: [
        {
          name: 'Herr Mikus B.',
          position: 'Heir Direktor',
          phone: '+371 28039182',
          email: 'heirdirektor@sivens.lv',
          photo: 'OwnerMB.jpg',
        },
        {
          name: 'Herr Robert P.R.',
          position: 'Chief Tehnology Officer',
          phone: '+371 19273012',
          email: 'xxgamerlord69xx@outlook.com',
          photo: 'CFO.jpg',
        },
        {
          name: 'Sidnijs J.',
          position: 'Chief Communications Officer',
          phone: '+371 26819378',
          email: 'heircomms@sivens.lv',
          photo: 'Chief_Social.jpg',
        },
        {
          name: 'Kristers E-A.',
          position: 'Lead Reviewer',
          phone: '+371 17292039',
          email: 'keareview@sivens.lv',
          photo: 'Lead_reviewer.jpg',
        },
        {
          name: 'Mārcis S-S.',
          position: 'Chief Maintenance Officer',
          phone: '+371 18490234',
          email: 'heircomms@sivens.lv',
          photo: 'Chief_janitor.jpg',
        },
        {
          name: 'Kristiāns K.',
          position: 'Chief Information and Truth Officer',
          phone: '+371 16938301',
          email: 'sivensministryofinformation@sivens.lv',
          photo: 'Information_miniter.jpg',
        },
      ],
    }
  },
  head() {
    return {
      title: 'About',
      meta: [
        {
          hid: 'description',
          name: 'description',
          content: 'We are a team, not only us, but you, the reader!',
        },
      ],
    }
  },
}
</script>
