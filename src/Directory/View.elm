module Directory.View exposing (view)

import DateFormat
import Directory.Commands exposing (CommunitiesRequestData)
import Directory.Communities
    exposing
        ( CohousingStatus(..)
        , CommunityDetails
        , CommunityID(..)
        , CommunityListing
        , CommunityStatus(..)
        , CommunityType(..)
        , ImageData
        , IncomeSharing(..)
        , LandStatus(..)
        , LocationType(..)
        , MembersWelcome(..)
        , PublicAddressType(..)
        , VisitorsWelcome(..)
        , addressTypeToString
        , cohousingStatusToString
        , incomeSharingToString
        , landStatusToString
        , locationTypeToString
        , membersWelcomeToString
        , statusToString
        , typeToString
        , visitorsWelcomeToString
        )
import Directory.Messages exposing (Msg(..))
import Directory.Model exposing (Model)
import Directory.Pagination as Pagination exposing (Pagination)
import Directory.Routing as Routing exposing (FilterParam(..), ListingsRoute(..), Route(..), reverse)
import Gallery
import Html exposing (Html, text)
import Html.Attributes exposing (alt, checked, class, height, href, id, name, src, target, title, type_, value, width)
import Html.Events exposing (onClick, onInput, onSubmit, preventDefaultOn)
import Html.Keyed as Keyed
import Json.Decode as Decode
import Map
import Markdown
import RemoteData exposing (WebData)
import String.Conversions exposing (fromHttpError)
import Time exposing (Posix, Zone)
import Time.Distance


{-| Render a Link to an Internal Application Page.
-}
navigateLink : Route -> String -> String -> Html Msg
navigateLink route classes content =
    Html.a (class classes :: navigateAttributes route) [ text content ]


{-| Build the Html Attributes for an Internal Application Link.
-}
navigateAttributes : Route -> List (Html.Attribute Msg)
navigateAttributes route =
    [ href <| reverse route, onClickNoDefault <| NavigateTo route ]


onClickNoDefault : msg -> Html.Attribute msg
onClickNoDefault msg =
    preventDefaultOn "click" <| Decode.succeed ( msg, True )


{-| Return an HTML Element or an Empty Node.
-}
maybeHtml : (a -> Html msg) -> Maybe a -> Html msg
maybeHtml viewFunction =
    Maybe.map viewFunction >> Maybe.withDefault (text "")


{-| Render the Application's State.
-}
view : Model -> Html Msg
view model =
    case model.route of
        ListingsRoute listingsRoute ->
            listingsView model.communities model.searchString model.currentDate listingsRoute

        DetailsRoute _ ->
            detailsView model.currentDate model.community model.communityGallery model.communityValidation


{-| Render a Details Page.
-}
detailsView :
    Maybe ( Posix, Zone )
    -> WebData CommunityDetails
    -> Gallery.Model ImageData
    -> WebData Bool
    -> Html Msg
detailsView currentDate community gallery validation =
    case community of
        RemoteData.NotAsked ->
            Html.div [ class "text-danger text-center my-4" ]
                [ text "Sorry, we encountered a problem when trying to load the Community, please try again or contact "
                , Html.a [ href "mailto:directory@ic.org" ] [ text "directory@ic.org" ]
                , text "."
                ]

        RemoteData.Loading ->
            loadingBar

        RemoteData.Failure err ->
            Html.div [ class "text-danger text-center my-4" ]
                [ text "Sorry, we encountered a problem when trying to load the Community, please try again or contact "
                , Html.a [ href "mailto:directory@ic.org" ] [ text "directory@ic.org" ]
                , text "."
                , Html.p [] [ text <| fromHttpError err ]
                ]

        RemoteData.Success details ->
            communityDetails currentDate details gallery validation


loadingBar : Html msg
loadingBar =
    Html.div [ class "tall" ]
        [ Html.div [ class "text-primary text-center" ] [ text "Loading..." ]
        , Html.div [ class "progress align-middle" ]
            [ Html.div [ class "progress-bar progress-bar-striped progress-bar-animated w-100" ] [] ]
        ]


communityDetails : Maybe ( Posix, Zone ) -> CommunityDetails -> Gallery.Model ImageData -> WebData Bool -> Html Msg
communityDetails maybeCurrentDate community communityGallery communityValidation =
    let
        area =
            [ community.city, community.state, community.country ]
                |> List.filter ((/=) "")
                |> String.join ", "
                |> text

        header =
            Html.div [ class "clearfix" ]
                [ Html.div [ class "float-left" ]
                    [ Html.h1 [ class "mb-1" ] [ text community.name ]
                    , Html.h2 [] [ Html.small [] [ area ] ]
                    , if community.status == Disbanded then
                        Html.strong [ class "text-danger" ] [ text "Disbanded Community" ]

                      else
                        text ""
                    ]
                , Html.ul [ class "text-right small text-muted float-right list-unstyled" ]
                    [ renderIf community.isAdmin <|
                        Html.li []
                            [ Html.a [ href adminEditLink ] [ text "Edit Listing" ] ]
                    , renderIf community.isOwner <|
                        Html.li []
                            [ Html.a [ href ownerEditLink ] [ text "Edit Listing" ]
                            ]
                    , renderIf (community.isOwner || community.isAdmin) <|
                        Html.li [] [ verifyLink ]
                    , Html.li [] <| updatedOn maybeCurrentDate community
                    , Html.li [] <| createdOn maybeCurrentDate community
                    ]
                ]

        idParam =
            (\(CommunityID i) -> String.fromInt i) community.id

        adminEditLink =
            "/wp-admin/admin.php?page=formidable-entries&frm_action=edit&id=" ++ idParam

        ownerEditLink =
            "/directory/edit-listing/?frm_action=edit&entry=" ++ idParam

        verifyLink =
            case communityValidation of
                RemoteData.NotAsked ->
                    Html.a [ href "#", onClickNoDefault VerifyCommunityClicked ]
                        [ text "Verify as Up-to-Date" ]

                RemoteData.Loading ->
                    Html.div [ class "text-primary font-weight-bold" ]
                        [ text "Validating Your Listing..." ]

                RemoteData.Success True ->
                    Html.div [ class "text-success font-weight-bold" ]
                        [ text "Listing Successfully Verified." ]

                RemoteData.Success False ->
                    Html.div [ class "text-danger font-weight-bold" ]
                        [ text "Your Listing is Incomplete, Please Update It." ]

                RemoteData.Failure _ ->
                    Html.div [ class "text-danger font-weight-bold" ]
                        [ text "An Error Occurred, Please Try Editing Your Listing." ]

        leftColumn =
            Html.div [ class "col-24 col-sm-14" ]
                [ renderJust community.image primaryImage
                , Html.h2 [] [ text "Mission Statement" ]
                , Html.p [] [ text community.missionStatement ]
                , Html.h2 [] [ text "Community Description" ]
                , Markdown.toHtml [] community.description
                ]

        primaryImage : ImageData -> Html Msg
        primaryImage ({ imageUrl, thumbnailUrl } as image) =
            let
                imageSrc =
                    Maybe.withDefault imageUrl thumbnailUrl

                linkWrapper inner =
                    Html.a
                        [ href imageUrl
                        , target "_blank"
                        , Gallery.open GalleryMsg image
                        ]
                        [ inner ]
            in
            Html.div [ class "mb-2" ]
                [ linkWrapper <|
                    Html.img
                        [ src imageSrc
                        , alt community.name
                        , title community.name
                        , class "img-thumbnail img-fluid d-block mx-auto"
                        ]
                        []
                ]

        section sectionHeader content =
            sectionHtml sectionHeader <| Html.p [] [ text content ]

        sectionHtml sectionHeader htmlContent =
            Html.div [] [ Html.h3 [] [ text sectionHeader ], htmlContent ]

        embedYoutube =
            case community.youtubeIds of
                [] ->
                    text ""

                firstId :: otherIds ->
                    sectionHtml "Video Gallery" <|
                        Html.iframe
                            [ type_ "text/html"
                            , class "embed-responsive mb-3"
                            , width 720
                            , height 480
                            , src
                                ("//www.youtube.com/embed/"
                                    ++ firstId
                                    ++ "?html5=1&origin=https://www.ic.org"
                                    ++ "&playlist="
                                    ++ String.join "," otherIds
                                )
                            ]
                            []

        fairHousingSection =
            if community.fairHousingComplaint then
                Html.div []
                    [ Html.h3 [] [ text "Fair Housing Laws" ]
                    , Html.p []
                        [ text <|
                            "This community acknowledges that their listing "
                                ++ "does not include any potential violations "
                                ++ "of the Fair Housing Law, or that they do "
                                ++ "not provide housing. For any questions "
                                ++ "about this topic please see our "
                        , Html.a [ href "/policies/", target "_blank" ]
                            [ text "Content Policies" ]
                        , text " and contact FIC with any questions or concerns: "
                        , Html.a [ href "mailto:directory@ic.org", target "_blank" ]
                            [ text "directory@ic.org" ]
                        , text "."
                        ]
                    ]

            else
                text ""

        galleryConfig =
            Gallery.Config .thumbnailUrl .imageUrl

        affiliations =
            List.filter (not << String.isEmpty) <|
                community.networkAffiliations
                    ++ [ community.otherAffiliations ]
    in
    Html.div [ class "directory-listing" ]
        [ header
        , Html.div [ class "row mb-2" ]
            [ leftColumn
            , detailRightColumn community
            ]
        , detailInfoBlocks community
        , renderMaybeString community.additionalComments <|
            section "Additional Comments"
        , renderIf (not <| List.isEmpty community.galleryImages) <|
            sectionHtml "Photo Gallery" <|
                Html.map GalleryMsg <|
                    Gallery.thumbnails galleryConfig community.galleryImages
        , embedYoutube
        , renderIf (not <| List.isEmpty affiliations) <|
            section "Community Network or Organization Affiliations" <|
                String.join ", " affiliations
        , renderMaybeString community.communityAffiliations <|
            section "Community Affiliations"
        , fairHousingSection
        , renderNonEmpty community.keywords <|
            section "Keywords"
        , Html.map GalleryMsg <|
            Gallery.modal communityGallery
        ]


{-| Render non-empty strings only.
-}
renderNonEmpty : String -> (String -> Html msg) -> Html msg
renderNonEmpty value_ renderer =
    if value_ == "" then
        text ""

    else
        renderer value_


{-| Render the value if present.
-}
renderJust : Maybe a -> (a -> Html msg) -> Html msg
renderJust value_ renderer =
    Maybe.map renderer value_ |> Maybe.withDefault (text "")


{-| Render a non-empty string if present & a blank node otherwise.
-}
renderMaybeString : Maybe String -> (String -> Html msg) -> Html msg
renderMaybeString m f =
    renderJust m <| \a -> renderNonEmpty a f


renderIf : Bool -> Html msg -> Html msg
renderIf condition html =
    if condition then
        html

    else
        text ""


detailRightColumn : CommunityDetails -> Html msg
detailRightColumn community =
    let
        rightColumn =
            [ boldLabel "Status" <| renderStatus community.status
            , boldLabelText "Started Planning" <| String.fromInt community.startedPlanning
            , boldLabelText "Started Living Together" <| String.fromInt community.startedLivingTogether
            , boldLabel "Visitors Accepted" <| visitorsWelcome community.openToVisitors
            , boldLabel "Open to New Members" <| membersWelcome community.openToMembers
            , Html.li [ class "text-center mt-2 mb-3" ]
                [ Html.small [ class "text-muted" ]
                    [ text "Please read the details in "
                    , Html.a [ href "#Membership" ] [ text "Membership" ]
                    , text " below before contacting this community."
                    ]
                , Html.a
                    [ class "my-1 btn btn-block btn-warning"
                    , href <|
                        "/directory/contact-a-community/?cmty="
                            ++ (\(CommunityID i) -> String.fromInt i) community.id
                    ]
                    [ text "Send Message"
                    ]
                ]
            , urlItem "Website" community.websiteUrl
            , urlItem "Business, Project, or Organization" community.businessUrl
            , urlItem "Facebook" community.facebookUrl
            , urlItem "Twitter" community.twitterUrl
            , urlItem "Other Social" community.socialUrl
            , renderNonEmpty community.contactName <| boldLabelText "Contact Name"
            , renderNonEmpty community.contactPhone <| boldLabelText "Phone"
            , renderJust community.contactAddress renderAddress
            , renderIf community.isFicMember <|
                ficBadge community.ficMembershipStartYear
            , renderJust community.disbandedInfo <|
                extraStatusInfo "This Community Has Disbanded" "Year Disbanded"
            , renderJust community.reformingInfo <|
                extraStatusInfo "This Community Is Re-Forming" "Year Re-Formed"
            , renderJust community.mapCoordinates <|
                \coords ->
                    Html.li []
                        [ Html.h3 [ class "text-center" ] [ text "Location" ]
                        , Map.render <| googleMap coords
                        ]
            ]

        urlItem name_ value_ =
            renderNonEmpty value_ <| boldLabel name_ << textLink

        renderAddress address =
            Html.li []
                [ Html.b [] [ text <| addressTypeToString address.addressType ++ ":" ]
                , Html.address [] <|
                    List.intersperse (Html.br [] []) <|
                        List.map text <|
                            List.filter ((/=) "")
                                [ address.lineOne
                                , address.lineTwo
                                , (\s -> s ++ " " ++ address.zipCode) <|
                                    String.join ", " <|
                                        List.filter ((/=) "") <|
                                            [ community.city, community.state ]
                                , community.country
                                ]
                ]

        ficBadge startYear =
            Html.li []
                [ Html.h3 [ class "text-center" ]
                    [ Html.a [ href "/community-bookstore/product/fic-membership/", target "_blank" ]
                        [ text "FIC Membership" ]
                    ]
                , Html.div [ class "text-center mb-3" ]
                    [ renderNonEmpty startYear <| \year -> Html.small [] [ text <| "Since " ++ year ]
                    , Html.a [ href "/community-bookstore/product/fic-membership/", target "_blank" ]
                        [ Html.img
                            [ id "fic-membership-badge"
                            , title "FIC Membership Badge"
                            , src "/wp-content/themes/fic-theme/img/membership-badge.png?v=1"
                            , alt "This Community is an FIC Member"
                            , class "img-fluid"
                            ]
                            []
                        ]
                    ]
                ]

        extraStatusInfo title_ label info =
            Html.li []
                [ Html.div []
                    [ Html.h3 [ class "text-center" ] [ text title_ ]
                    , renderNonEmpty info.year <| boldLabelText label
                    , renderNonEmpty info.info <| \i -> Html.p [] [ text i ]
                    ]
                ]

        googleMap coords =
            { center = Just coords
            , zoom = 7
            , markers = [ Map.Marker coords community.name ]
            }

        textLink url =
            Html.a [ href url, target "_blank" ] [ text url ]

        boldLabel label value_ =
            Html.li [] [ Html.b [] [ text <| label ++ ":" ], text " ", value_ ]

        boldLabelText label value_ =
            boldLabel label <| text value_
    in
    Html.div [ class "col-24 col-sm-10" ]
        [ Html.div [ class "card" ]
            [ Html.ul [ class "card-body list-unstyled listing-status mb-0" ] rightColumn
            ]
        ]


detailInfoBlocks : CommunityDetails -> Html msg
detailInfoBlocks community =
    let
        infoBlock header content =
            Html.div [ class "card" ]
                [ Html.h3 [ class "card-header" ] [ text header ]
                , Html.div [ class "card-body" ]
                    [ Html.ul [ class "list-unstyled pl-0 mb-0" ] <|
                        List.map infoItem <|
                            List.concat content
                    ]
                ]

        infoItem ( title_, content ) =
            Html.li [ class "pb-2" ]
                [ Html.b [] [ text title_, text ":" ], text " ", content ]

        infoBlockSublist header l =
            case l of
                [] ->
                    []

                x :: [] ->
                    [ ( header, text x ) ]

                _ ->
                    [ ( header
                      , Html.ul [] <| List.map (\c -> Html.li [] [ text c ]) l
                      )
                    ]

        maybeInfoItem header maybeContent toHtml =
            Maybe.map (List.singleton << (\c -> ( header, c )) << toHtml) maybeContent
                |> Maybe.withDefault []

        stringInfoItem header value_ =
            if String.isEmpty value_ then
                []

            else
                [ ( header, text value_ ) ]

        landAmountAndUnits amount =
            text <|
                String.fromFloat amount
                    ++ " "
                    ++ Maybe.withDefault "" community.landSizeUnits

        boolToString val =
            if val then
                "Yes"

            else
                "No"
    in
    Html.div [ class "card-columns listing-info-blocks" ]
        [ infoBlock "About"
            [ infoBlockSublist "Type(s)" <| List.map typeToString community.communityTypes
            , infoBlockSublist "Programs & Activities" community.programsAndActivites
            , [ ( "Location", text <| locationTypeToString community.location ) ]
            ]
        , infoBlock "Housing"
            [ maybeInfoItem "Status"
                community.landStatus
                (text << landStatusToString)
            , maybeInfoItem "Area" community.landSizeAmount landAmountAndUnits
            , infoBlockSublist "Current Residence Types" community.currentResidenceTypes
            , maybeInfoItem "Current Number of Residences"
                community.currentResidences
                (text << String.fromInt)
            , maybeInfoItem "Planned Number of Residences"
                community.plannedResidences
                (text << String.fromInt)
            , infoBlockSublist "Planned Residence Types" community.plannedResidenceTypes
            , infoBlockSublist "Housing Provided" community.housingAccess
            , maybeInfoItem "Land Owned By" community.landOwner text
            , maybeInfoItem "Additional Comments" community.housingComments text
            ]
        , infoBlock "Membership"
            [ [ ( "Adult Members", text <| String.fromInt community.adultCount ) ]
            , maybeInfoItem "Child Members" community.childCount (text << String.fromInt)
            , maybeInfoItem "Non-Member Residents" community.nonmemberCount (text << String.fromInt)
            , maybeInfoItem "Percent Women" community.percentFemale text
            , maybeInfoItem "Percent Men" community.percentMale text
            , maybeInfoItem "Percent Transgender" community.percentTrans text
            , maybeInfoItem "Percent Non-Binary" community.percentNonBinary text
            , [ ( "Visitors Accepted", visitorsWelcome community.openToVisitors ) ]
            , maybeInfoItem "Visitor Process" community.visitorProcess text
            , [ ( "Open to New Members", membersWelcome community.openToMembers ) ]
            , maybeInfoItem "Membership Process" community.membershipProcess text
            , maybeInfoItem "Additional Comments" community.membershipComments text
            ]
        , infoBlock "Government"
            [ [ ( "Decision Making", text community.decisionMaking )
              , ( "Identified Leader", text community.leader )
              ]
            , maybeInfoItem "Leadership Core Group" community.leadershipGroup text
            , maybeInfoItem "Additional Comments" community.governmentComments text
            ]
        , infoBlock "Economics"
            [ if community.hasJoinFee then
                maybeInfoItem "Join Fee($)" community.joinFee (text << String.fromFloat)

              else
                []
            , [ ( "Dues, Fees, or Shared Expenses"
                , text <|
                    if community.hasRegularFees then
                        "Yes"

                    else
                        "No"
                )
              ]
            , if community.hasRegularFees then
                maybeInfoItem "Monthly Fees($)" community.regularFees (text << String.fromFloat)

              else
                []
            , [ ( "Shared Income", text <| incomeSharingToString community.sharedIncome ) ]
            , if community.contributeLabor == "Yes" then
                maybeInfoItem "Required Weekly Labor Contribution"
                    community.laborHours
                    (text << String.fromFloat)

              else if community.contributeLabor == "No" then
                []

              else
                maybeInfoItem "Suggested Weekly Labor Contribution"
                    community.laborHours
                    (text << String.fromFloat)
            , maybeInfoItem "Open to Members with Existing Debt" community.memberDebt text
            , maybeInfoItem "Additional Comments" community.economicsComments text
            ]
        , infoBlock "Sustainability Practices"
            [ maybeInfoItem "Energy Infrastructure" community.energyInfrastructure text
            , maybeInfoItem "Current Renewable Energy Generation" community.currentRenewablePercentage text
            , infoBlockSublist "Energy Sources" <| Maybe.withDefault [] community.renewableSources
            , maybeInfoItem "Planned Renewable Energy Generation" community.plannedRenewablePercentage text
            , maybeInfoItem "Current Food Produced" community.currentFoodPercentage text
            , maybeInfoItem "Planned Food Produced" community.plannedFoodPercentage text
            , maybeInfoItem "Food Produced Locally" community.localFoodPercentage text
            ]
        , infoBlock "Lifestyle"
            [ infoBlockSublist "Common Facilities" community.facilities
            , maybeInfoItem "Internet Available" community.internetAccess text
            , maybeInfoItem "Internet Fast?" community.internetSpeed text
            , maybeInfoItem "Cellphone Service" community.cellService text
            , maybeInfoItem "Shared Meals" community.sharedMeals text
            , infoBlockSublist "Dietary Practices" community.dietaryPractices
            , maybeInfoItem "Dietary Choice or Restrictions" community.commonDiet text
            , maybeInfoItem "Special Diets OK" community.specialDiets text
            , maybeInfoItem "Alcohol Use" community.alcohol text
            , maybeInfoItem "Tobacco Use" community.tobacco text
            , maybeInfoItem "Additional Diet Comments" community.dietComments text
            , if not (List.isEmpty community.spiritualPractices) then
                infoBlockSublist "Common Spiritual Practice(s)" community.spiritualPractices
                    ++ maybeInfoItem "Spiritual Practice Expected?" community.religionExpected text

              else
                []
            , infoBlockSublist "Education Style(s)" community.education
            , maybeInfoItem "Expected Healthcare Practices" community.healthcareComments text
            , infoBlockSublist "Healthcare Options" community.healthcareOptions
            , maybeInfoItem "Additional Comments" community.lifestyleComments text
            ]
        , (\a b -> maybeHtml b a) community.cohousing <|
            \cohousing ->
                infoBlock "Cohousing"
                    [ maybeInfoItem "Building Site Status"
                        cohousing.siteStatus
                        (cohousingStatusToString >> text)
                    , maybeInfoItem "Year Construction Completed"
                        cohousing.yearCompleted
                        (String.fromInt >> text)
                    , maybeInfoItem "Number of Housing Units"
                        cohousing.housingUnits
                        (String.fromInt >> text)
                    , maybeInfoItem "Has a Shared Common Building"
                        cohousing.hasSharedBuilding
                        (boolToString >> text)
                    , stringInfoItem "Architect" cohousing.architect
                    , stringInfoItem "Developer" cohousing.developer
                    , stringInfoItem "Commercial Lender" cohousing.lender
                    ]
        ]


renderStatus : CommunityStatus -> Html msg
renderStatus status =
    let
        statusClass =
            if status == Disbanded then
                "text-danger"

            else
                ""
    in
    Html.span [ class statusClass ] [ text <| statusToString status ]


visitorsWelcome : VisitorsWelcome -> Html msg
visitorsWelcome welcomeStatus =
    let
        visitorsWelcomeClass welcomeStatus_ =
            case welcomeStatus_ of
                Welcome ->
                    "text-success"

                Rarely ->
                    "text-warning"

                NoVisitors ->
                    "text-danger"
    in
    Html.span [ class <| visitorsWelcomeClass welcomeStatus ]
        [ text <| visitorsWelcomeToString welcomeStatus ]


membersWelcome : MembersWelcome -> Html msg
membersWelcome welcomeStatus =
    let
        membersWelcomeClass welcomeStatus_ =
            case welcomeStatus_ of
                Yes ->
                    "text-success"

                Waitlist ->
                    "text-warning"

                NoMembers ->
                    "text-danger"
    in
    Html.span [ class <| membersWelcomeClass welcomeStatus ]
        [ text <| membersWelcomeToString welcomeStatus ]


{-| Return the text to display for a Listing's Updated Date.
-}
updatedOn : Maybe ( Posix, Zone ) -> { a | updatedAt : Posix } -> List (Html msg)
updatedOn currentDate community =
    [ Html.b [] [ text "Updated on: " ]
    , text <| dateFormat (Maybe.map Tuple.second currentDate) community.updatedAt
    , timeAgo (Maybe.map Tuple.first currentDate) community.updatedAt
    ]


createdOn : Maybe ( Posix, Zone ) -> { a | createdAt : Posix } -> List (Html msg)
createdOn currentDate community =
    [ Html.b [] [ text "Created on: " ]
    , text <| dateFormat (Maybe.map Tuple.second currentDate) community.createdAt
    , timeAgo (Maybe.map Tuple.first currentDate) community.createdAt
    ]


dateFormat : Maybe Zone -> Posix -> String
dateFormat maybeZone =
    DateFormat.format
        [ DateFormat.monthNameAbbreviated
        , DateFormat.text " "
        , DateFormat.dayOfMonthNumber
        , DateFormat.text ", "
        , DateFormat.yearNumber
        ]
        (Maybe.withDefault Time.utc maybeZone)


{-| Return text saying how long ago a date was. Or nothing if we do not have
the current date.
-}
timeAgo : Maybe Posix -> Posix -> Html msg
timeAgo maybeCurrentDate date =
    maybeCurrentDate
        |> maybeHtml
            (\currentDate ->
                text <| " (" ++ Time.Distance.inWords currentDate date ++ " ago)"
            )


{-| Render a Listings Page.
-}
listingsView :
    Pagination CommunityListing CommunitiesRequestData
    -> String
    -> Maybe ( Posix, Zone )
    -> ListingsRoute
    -> Html Msg
listingsView communities searchString currentDate route =
    let
        maybeRssLink =
            case route of
                RecentlyAdded _ _ ->
                    Just "/rss-newly-listed-directory-listings/"

                RecentlyUpdated _ _ ->
                    Just "/rss-recently-updated-community-listings/"

                _ ->
                    Nothing

        rssIcon =
            Maybe.map
                (\link ->
                    Html.a [ href link, class "float-right" ]
                        [ Html.img
                            [ src "/wp-content/uploads/2014/01/RSS_icon.png"
                            , width 32
                            , height 32
                            , alt "RSS Feed"
                            ]
                            []
                        ]
                )
                >> Maybe.withDefault (text "")

        pageHeading =
            Html.div [ class "clearfix directory-rss" ]
                [ rssIcon maybeRssLink
                , Html.h1 [ class "page-title" ] [ text <| Routing.getPageTitle route ]
                ]

        listings =
            Html.div []
                [ searchForm
                , links
                , Html.div [ class "clearfix" ]
                    [ if not <| List.isEmpty (Pagination.getCurrent communities) then
                        resultCount communities

                      else
                        text ""
                    , filterHtml route
                    ]
                , communitiesList
                , if not <| List.isEmpty (Pagination.getCurrent communities) then
                    Html.div []
                        [ links
                        , Html.ul [ class "pagination justify-content-center" ] <|
                            pagination route communities
                        ]

                  else
                    text ""
                ]

        searchForm =
            Html.form [ class "justify-content-center form-inline", onSubmit SubmitSearchForm ]
                [ Html.input
                    [ class "d-inline-block mr-2 form-control"
                    , value searchString
                    , onInput UpdateSearchString
                    , name "search"
                    ]
                    []
                , Html.input
                    [ class "btn btn-primary"
                    , type_ "submit"
                    , value "Search"
                    ]
                    []
                ]

        communitiesList =
            if Pagination.isLoading communities then
                loadingBar

            else if Pagination.getError communities /= Nothing then
                Html.div [ class "text-danger text-center my-4" ]
                    [ text "Sorry, we encountered a problem when trying to load Communities, please try again or contact "
                    , Html.a [ href "mailto:directory@ic.org" ] [ text "directory@ic.org" ]
                    , text "."
                    ]

            else if Pagination.hasNone communities then
                Html.div [ class "tall text-danger text-center" ]
                    [ text "Sorry, we couldn't find any matching Communities." ]

            else
                Html.div [ class "list-group directory-listings mt-2" ] <|
                    List.map (communityItem currentDate) <|
                        Pagination.getCurrent communities
    in
    Html.div []
        [ pageHeading
        , listings
        ]


{-| Render the Links Appearing Above & Below the Listings.
-}
links : Html Msg
links =
    let
        staticLinks =
            List.map
                (\( content, slug ) ->
                    Html.a [ href <| "/directory/" ++ slug ] [ text content ]
                )
                [ ( "Types", "community-types" )
                , ( "State/Country List", "intentional-communities-by-country" )
                , ( "Maps", "map" )
                , ( "Advanced Search", "search" )
                ]

        pageLinks =
            List.map
                (\( content, route ) ->
                    navigateLink (ListingsRoute <| route 1 []) "" content
                )
                [ ( "Newest Communities", RecentlyAdded )
                , ( "Recently Updated", RecentlyUpdated )
                ]
    in
    Html.div [ class "directory-header-links" ] <|
        List.intersperse (text " | ") (pageLinks ++ staticLinks)


{-| Render the Result Count if there are Results.
-}
resultCount : Pagination CommunityListing CommunitiesRequestData -> Html Msg
resultCount communityPagination =
    if not <| Pagination.hasNone communityPagination then
        Html.div [ class "float-left" ]
            [ text "Showing "
            , Html.b [] [ text <| String.fromInt <| Pagination.getTotalItems communityPagination ]
            , text " communities."
            ]

    else
        text ""


{-| Render the Filter Checkbox Inputes.
-}
filterHtml : ListingsRoute -> Html Msg
filterHtml route =
    let
        currentFilters =
            List.foldl
                (\inherentFilter extraFilters ->
                    List.filter (\f -> f /= inherentFilter) extraFilters
                )
                (Routing.getAdditionalFilters route)
                (Routing.getInherentFilters route)

        annotate filter =
            (\maybeAnnotation -> ( filter, maybeAnnotation, List.member filter currentFilters )) <|
                case filter of
                    VisitorsFilter ->
                        Just ( "visitors", "Visitors Welcome" )

                    MembersFilter ->
                        Just ( "members", "Accepting Members" )

                    EstablishedFilter ->
                        Just ( "established", "Established" )

                    FormingFilter ->
                        Just ( "forming", "Forming" )

                    FICMemberFilter ->
                        Just ( "fic-member", "FIC Member" )

                    _ ->
                        Nothing

        checkMsg filter isOn =
            NavigateTo
                << ListingsRoute
                << Routing.toPageOne route
            <|
                if isOn then
                    List.filter (\f -> f /= filter) currentFilters

                else
                    filter :: currentFilters

        render ( filter, maybeNameAndText, isOn ) =
            case maybeNameAndText of
                Nothing ->
                    text ""

                Just ( filterName, filterText ) ->
                    Html.label [ onClick <| checkMsg filter isOn ]
                        [ Html.input
                            [ type_ "checkbox"
                            , name filterName
                            , checked isOn
                            ]
                            []
                        , Html.span [] [ text <| " " ++ filterText ]
                        ]
    in
    Html.div [ class "float-right directory-filters" ] <|
        List.map (annotate >> render) Routing.inlineFilters


{-| Render the Pagination for the Listings.
-}
pagination : ListingsRoute -> Pagination CommunityListing CommunitiesRequestData -> List (Html Msg)
pagination route communityPagination =
    let
        currentPage =
            Pagination.getPage communityPagination

        backArrow =
            if Pagination.hasPrevious communityPagination then
                Html.li [ class "page-item" ]
                    [ navigateLink (ListingsRoute <| Routing.mapPage (always <| currentPage - 1) route)
                        "page-link"
                        "«"
                    ]

            else
                Html.li [ class "page-item disabled" ]
                    [ Html.a [ class "page-link" ] [ text "«" ] ]

        forwardArrow =
            if Pagination.hasNext communityPagination then
                Html.li [ class "page-item" ]
                    [ navigateLink (ListingsRoute <| Routing.mapPage (always <| currentPage + 1) route)
                        "page-link"
                        "»"
                    ]

            else
                Html.li [ class "page-item disabled" ]
                    [ Html.a [ class "page-link" ] [ text "»" ] ]

        lastPage =
            Pagination.getTotalPages communityPagination

        showMiddle =
            currentPage > 4 && currentPage < lastPage - 3

        dots =
            Html.li [ class "page-item disabled" ] [ Html.span [ class "page-link" ] [ text "..." ] ]

        splitSections =
            lastPage > 8

        firstNumbers =
            if splitSections && not showMiddle then
                List.range 1 4
                    |> List.map pageLink

            else if splitSections then
                List.range 1 2
                    |> List.map pageLink

            else
                List.range 1 lastPage
                    |> List.map pageLink

        middleNumbers =
            if showMiddle then
                List.range (currentPage - 2) (currentPage + 2)
                    |> List.map pageLink

            else
                []

        endNumbers =
            if splitSections && not showMiddle then
                List.range (lastPage - 3) lastPage
                    |> List.map pageLink

            else if splitSections then
                List.range (lastPage - 1) lastPage
                    |> List.map pageLink

            else
                []

        pageLink page =
            if page == currentPage then
                Html.li [ class "page-item active" ]
                    [ Html.span [ class "page-link" ] [ text <| String.fromInt page ] ]

            else
                Html.li [ class "page-item" ]
                    [ navigateLink (ListingsRoute <| Routing.mapPage (always page) route)
                        "page-link"
                        (String.fromInt page)
                    ]
    in
    List.concat
        [ [ backArrow ]
        , firstNumbers
        , [ if showMiddle then
                dots

            else
                text ""
          ]
        , middleNumbers
        , [ if splitSections then
                dots

            else
                text ""
          ]
        , endNumbers
        , [ forwardArrow ]
        ]


{-| Render a Single Community in the Listings.
-}
communityItem : Maybe ( Posix, Zone ) -> CommunityListing -> Html Msg
communityItem maybeCurrentDate community =
    let
        address { city, state, country } =
            [ city, state, country ]
                |> List.filter (not << String.isEmpty)
                |> String.join ", "

        imageElement name_ imageUrl =
            Keyed.node "div"
                [ class "text-center text-sm-left" ]
                [ ( "list-item-image-" ++ community.slug
                  , Html.img
                        [ src imageUrl
                        , alt name_
                        , class "float-sm-left img-thumbnail mr-sm-2 mb-1"
                        ]
                        []
                  )
                ]

        maybeImage image =
            maybeHtml (imageElement image.name) image.thumbnailUrl
    in
    Html.a
        (class "list-group-item list-group-item-action"
            :: navigateAttributes (DetailsRoute community.slug)
        )
        [ Html.div [ class "mb-2 w-100" ]
            [ Html.div [ class "clearfix" ]
                [ maybeImage community
                , Html.h2 []
                    [ text community.name
                    , Html.br [] []
                    , Html.small []
                        [ text <| address community ++ " - "
                        , Html.em []
                            [ renderStatus community.status ]
                        ]
                    ]
                , Html.div []
                    [ Html.div []
                        [ Html.b [] [ text "Visitors Accepted: " ]
                        , visitorsWelcome community.openToVisitors
                        ]
                    , Html.div []
                        [ Html.b []
                            [ text "Open to New Members: " ]
                        , membersWelcome community.openToMembers
                        ]
                    ]
                , Html.div []
                    [ Html.b [] [ text "Community Types: " ]
                    , text <|
                        String.join ", " <|
                            List.map typeToString community.communityTypes
                    ]
                ]
            ]
        , Html.div [ class "small text-muted" ] <|
            updatedOn maybeCurrentDate community
                ++ text " | "
                :: createdOn maybeCurrentDate community
        ]
