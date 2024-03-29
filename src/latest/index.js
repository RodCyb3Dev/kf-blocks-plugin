const { registerBlockType } = wp.blocks;
const { RichText} = wp.editor;
const { withSelect } = wp.data;

/** Import the logo */
import { ReactComponent as Logo } from '../kf-logo.svg';

registerBlockType('kf/latest', {
    title: 'Kodeflash Latest Articles', 
    icon: { src: Logo }, 
    category: 'kf-cat',
    edit: withSelect( select => {
        return {
            // Send a GET request to the WP REST API
            posts: select('core').getEntityRecords('postType', 'post', {
                per_page: 3,
            })
        };
    })(({ posts }) => {

        console.log(posts);

        // If no response yet
        if(!posts) {
            return 'Loading...';
        }

        // If no posts are returned
        if( posts && posts.length === 0) {
            return "There're no results";
        }
        
        return (
            <>
            <h1 className="latest-articles-heading">Latest Articles</h1>
            <ul className="latest-articles container">
                {posts.map(post => {
                    // console.log(post);

                    return(
                    <li>
                        <img src={post.article_image} />
                        <div className="content">
                            <h2>{post.title.rendered}</h2>
                            <p>
                               <RichText.Content value={post.content.rendered.substring(0, 180 ) + '...'} /> 
                            </p>
                            <a href={post.link} className="button">Read More</a>
                        </div>
                    </li>
                    )
                })}
                    
                </ul>
            </>
        )
    }),
    save: () => {
        return null;
    }
});